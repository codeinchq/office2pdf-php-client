<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace CodeInc\Office2PdfClient\Tests;

use CodeInc\Office2PdfClient\Exception;
use CodeInc\Office2PdfClient\Office2PdfClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

final class Office2PdfClientTest extends TestCase
{
    private const string DEFAULT_OFFICE2PDF_BASE_URL = 'http://localhost:3000';
    private const string TEST_DOC_PATH = __DIR__.'/assets/file.docx';
    private const string TEST_TEMP_PATH = '/tmp/file.pdf';

    public function testHealth(): void
    {
        // testing a healthy service
        $client = $this->getNewClient();
        $this->assertNotFalse($client->isHealthy(), "The service is not healthy.");

        // testing a non-existing service
        $client = new Office2PdfClient('https://example.com');
        $this->assertFalse($client->isHealthy(), "The service is healthy.");

        // testing a non-existing url
        $client = new Office2PdfClient('https://example-NQrkB6F6MwuXesMrBhqx.com');
        $this->assertFalse($client->isHealthy(), "The service is healthy.");
    }

    /**
     * Tests the method convert() with a DOCX file.
     *
     * @throws Exception
     */
    public function testConvert(): void
    {
        $this->assertIsWritable(
            dirname(self::TEST_TEMP_PATH),
            "The directory ".dirname(self::TEST_TEMP_PATH)." is not writable."
        );

        $client = $this->getNewClient();
        $stream = $client->convert($client->streamFactory->createStreamFromFile(self::TEST_DOC_PATH));
        $this->assertInstanceOf(StreamInterface::class, $stream, "The method convert() should return a stream.");

        $f = fopen(self::TEST_TEMP_PATH, 'w+');
        self::assertNotFalse($f, "The test file could not be opened");

        $r = stream_copy_to_stream($stream->detach(), $f);
        self::assertNotFalse($r, "The stream could not be copied to the test file");
        fclose($f);

        $this->assertFileExists(self::TEST_TEMP_PATH, "The converted file does not exist.");
        $this->assertStringContainsString(
            '%PDF-1.',
            file_get_contents(self::TEST_TEMP_PATH),
            "The file self::TEST_TEMP_PATH is not a PDF file."
        );

        unlink(self::TEST_TEMP_PATH);
    }

    /**
     * Tests a failure when trying to convert an unsupported file type.
     *
     * @return void
     * @throws Exception
     */
    public function testConvertUnsupportedFileType(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::ERROR_UNSUPPORTED_FILE_TYPE);
        $this->getNewClient()->convert("", "file.pdf");
    }

    /**
     * Tests the method supports().
     *
     * @return void
     */
    public function testSupport(): void
    {
        $client = $this->getNewClient();
        foreach (Office2PdfClient::SUPPORTED_EXTENSIONS as $extension) {
            $this->assertTrue(
                $client->supports("file.$extension"),
                "The method supports() should return true for a file with the extension $extension."
            );
        }

        $this->assertTrue(
            $client->supports("filename_without_extension"),
            "The method supports() should return true for a file without extension."
        );

        $this->assertFalse(
            $client->supports('file.pdf'),
            "The method supports() should return false for a PDF file."
        );
    }

    /**
     * Returns a new Office2PdfClient instance.
     *
     * @return Office2PdfClient
     */
    private function getNewClient(): Office2PdfClient
    {
        return new Office2PdfClient(
            defined('OFFICE2PDF_BASE_URL')
                ? constant('OFFICE2PDF_BASE_URL')
                : self::DEFAULT_OFFICE2PDF_BASE_URL
        );
    }
}