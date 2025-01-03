# Office2Pdf PHP client

[![Code Inc.](https://img.shields.io/badge/Code%20Inc.-Office2Pdf-blue)](https://github.com/codeinchq/office2pdf)
![Tests](https://github.com/codeinchq/office2pdf-php-client/actions/workflows/phpunit.yml/badge.svg)

> [!IMPORTANT]  
> This client is deprecated and is replaced by the [Document Cloud PHP Client](https://github.com/codeinchq/document-cloud-php-client)

This repository contains a PHP 8.2+ library for converting Office files to PDF using the [office2pdf](https://github.com/codeinchq/office2pdf) service.

## Installation

The library is available on [Packagist](https://packagist.org/packages/codeinc/office2pdf-client). The recommended way to install it is via Composer:
```bash
composer require codeinc/office2pdf-client
```

## Usage

This client requires a running instance of the [office2pdf](https://github.com/codeinchq/office2pdf) service. The service can be run locally [using Docker](https://hub.docker.com/r/codeinchq/office2pdf) or deployed to a server.

### Example:
```php
use CodeInc\Office2PdfClient\Office2PdfClient;
use CodeInc\Office2PdfClient\ConvertOptions;
use CodeInc\Office2PdfClient\Format;

$apiBaseUri = 'http://localhost:3000/';
$srcDocPath = '/path/to/local/file.docx';
$destPdfPath = '/path/to/local/file.pdf';
$convertOption = new ConvertOptions(
    firstPage: 2,
    lastPage: 3,
    format: Format::json
);

try {
    $client = new Office2PdfClient($apiBaseUri);

    // convert 
    $pdfStream = $client->convert(
        $client->createStreamFromFile($srcDocPath), 
        $convertOption
    );
    
   // save the PDF
   $client->saveStreamToFile($pdfStream, $destPdfPath); 
}
catch (Exception $e) {
    // handle exception
}
```

#### Validating the support of a file format:
```php

use CodeInc\Office2PdfClient\Office2PdfClient;
use CodeInc\Office2PdfClient\Exception;

$filename = 'a-file.docx';

$client = new Office2PdfClient('http://localhost:3000/');

$client->isSupported("a-file.docx"); // returns true
$client->isSupported("a-file"); // returns true 
$client->isSupported("a-file", false); // returns false (the second argument is the strict mode)
$client->isSupported("a-file.pdf"); // returns false
``` 

## License

The library is published under the MIT license (see [`LICENSE`](LICENSE) file).