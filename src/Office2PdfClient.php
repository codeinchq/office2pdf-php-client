<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace CodeInc\Office2PdfClient;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Message\MultipartStream\MultipartStreamBuilder;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class Office2PdfClient
 *
 * @package CodeInc\Office2PdfClient
 * @link https://github.com/codeinchq/office2pdf
 * @link https://github.com/codeinchq/office2pdf-php-client
 * @license https://opensource.org/licenses/MIT MIT
 */
class Office2PdfClient
{
    public const array SUPPORTED_EXTENSIONS = [
        'txt',
        'rtf',
        'fodt',
        'doc',
        'docx',
        'odt',
        'xls',
        'xlsx',
        'ods',
        'ppt',
        'pptx',
        'odp'
    ];

    public function __construct(
        private readonly string $baseUrl,
        private ClientInterface|null $client = null,
        private StreamFactoryInterface|null $streamFactory = null,
        private RequestFactoryInterface|null $requestFactory = null,
    ) {
        $this->client ??= Psr18ClientDiscovery::find();
        $this->streamFactory ??= Psr17FactoryDiscovery::findStreamFactory();
        $this->requestFactory ??= Psr17FactoryDiscovery::findRequestFactory();
    }

    /**
     * Converts an Office file to PDF using the OFFICE2PDF API.
     *
     * @param StreamInterface|resource|string $stream The PDF content as a stream, a resource or a string.
     * @param string $filename The filename associated with the stream (optional).
     * @param bool $skipTypeCheck If enabled, the method will not check if the file extension is supported.
     * @return StreamInterface The PDF content as a stream.
     * @throws Exception
     */
    public function convert(mixed $stream, string $filename = 'file', bool $skipTypeCheck = false): StreamInterface
    {
        // checking the file extension
        if (!$this->supports($filename) && !$skipTypeCheck) {
            throw new Exception(
                message: "The file '$filename' is not supported",
                code: Exception::ERROR_UNSUPPORTED_FILE_TYPE
            );
        }

        // building the multipart stream
        $multipartStreamBuilder = (new MultipartStreamBuilder($this->streamFactory))
            ->addResource(
                'file',
                $stream,
                [
                    'filename' => $filename,
                    'headers'  => ['Content-Type' => 'application/pdf']
                ]
            );

        // sending the request
        try {
            $response = $this->client->sendRequest(
                $this->requestFactory
                    ->createRequest("POST", $this->getConvertEndpointUri())
                    ->withHeader(
                        "Content-Type",
                        "multipart/form-data; boundary={$multipartStreamBuilder->getBoundary()}"
                    )
                    ->withBody($multipartStreamBuilder->build())
            );
        } catch (ClientExceptionInterface $e) {
            throw new Exception(
                message: "An error occurred while sending the request to the OFFICE2PDF API",
                code: Exception::ERROR_REQUEST,
                previous: $e
            );
        }

        // checking the response
        if ($response->getStatusCode() !== 200) {
            throw new Exception(
                message: "The OFFICE2PDF API returned an error {$response->getStatusCode()}",
                code: Exception::ERROR_RESPONSE,
                previous: new Exception((string)$response->getBody())
            );
        }

        // returning the response
        return $response->getBody();
    }

    /**
     * Converts a local Office file to a local PDF file using the OFFICE2PDF API.
     *
     * @param string $officePath The path to the input Office file.
     * @param string $pdfPath The path to the output PDF file.
     * @throws Exception
     */
    public function convertFile(string $officePath, string $pdfPath): void
    {
        // opening the file
        $src = fopen($officePath, 'r');
        if ($src === false) {
            throw new Exception(
                message: "The file '$officePath' could not be opened",
                code: Exception::ERROR_LOCAL_FILE
            );
        }

        // opening the destination file
        $dest = fopen($pdfPath, 'w');
        if ($dest === false) {
            throw new Exception(
                message: "The file '$pdfPath' could not be opened",
                code: Exception::ERROR_LOCAL_FILE
            );
        }

        // converting the file & copying the stream to the destination file
        $stream = $this->convert($src, basename($officePath));
        stream_copy_to_stream($stream->detach(), $dest);
        fclose($dest);
    }

    /**
     * Returns the convert endpoint URI.
     *
     * @return string
     */
    private function getConvertEndpointUri(): string
    {
        $url = $this->baseUrl;
        if (!str_ends_with($url, '/')) {
            $url .= '/';
        }
        return "{$url}convert";
    }

    /**
     * Verifies if the client supports a file.
     *
     * @param string $filename The filename.
     * @param bool $strictMode If enabled, the method will return true for files without extension.
     * @return bool
     */
    public function supports(string $filename, bool $strictMode = false): bool
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if ($extension) {
            return in_array(strtolower($extension), self::SUPPORTED_EXTENSIONS);
        }
        return !$strictMode;
    }
}