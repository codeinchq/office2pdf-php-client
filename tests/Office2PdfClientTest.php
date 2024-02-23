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

final class Office2PdfClientTest extends TestCase
{
    private const string DEFAULT_OFFICE2PDF_BASE_URL = 'http://localhost:3000';
    private const string TEST_DOC_PATH = __DIR__.'/assets/file.docx';
    private const string TEST_TEMP_PATH = __DIR__.'/temp';

    /**
     * Tests the method convert() with a DOCX file.
     *
     * @throws Exception
     */
    public function testConvert(): void
    {
        $this->assertIsWritable(self::TEST_TEMP_PATH, "The directory ".self::TEST_TEMP_PATH." is not writable.");

        $tempPdfFile = self::TEST_TEMP_PATH.'/file.pdf';
        if (file_exists($tempPdfFile)) {
            unlink($tempPdfFile);
        }

        $this->getNewClient()->convertFile(self::TEST_DOC_PATH, $tempPdfFile);
        $this->assertFileExists($tempPdfFile, "The file $tempPdfFile does not exist.");
        $this->assertGreaterThan(0, filesize($tempPdfFile), "The file $tempPdfFile is empty.");

        unlink($tempPdfFile);
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