# office2pdf PHP client

This repository contains a PHP 8.2+ library for converting Office files to PDF using the [office2pdf](https://github.com/codeinchq/office2pdf) service.

## Installation

The recommended way to install the library is through [Composer](http://getcomposer.org):

```bash
composer require codeinc/office2pdf-client
```

## Usage

This client requires a running instance of the [office2pdf](https://github.com/codeinchq/office2pdf) service. The service can be run locally [using Docker](https://hub.docker.com/r/codeinchq/office2pdf) or deployed to a server.

### Base example:

```php
use CodeInc\Office2PdfClient\Office2PdfClient;
use CodeInc\Office2PdfClient\Exception;

$apiBaseUri = 'http://localhost:3000/';
$localDocPath = '/path/to/local/file.docx';

try {
    // convert
    $client = new Office2PdfClient($apiBaseUri);
    $pdfStream = $client->convertFile($localDocPath);
    
    // display the text
    echo (string)$pdfStream;
}
catch (Exception $e) {
    // handle exception
}
```

### With options:

```php
use CodeInc\Office2PdfClient\Office2PdfClient;
use CodeInc\Office2PdfClient\ConvertOptions;
use CodeInc\Office2PdfClient\Format;

$apiBaseUri = 'http://localhost:3000/';
$localPdfPath = '/path/to/local/file.pdf';
$convertOption = new ConvertOptions(
    firstPage: 2,
    lastPage: 3,
    format: Format::json
);

try {
    // convert 
    $client = new Office2PdfClient($apiBaseUri);
    $jsonResponse = $client->convertFile($localPdfPath, $convertOption);
    $decodedJson = $client->processJsonResponse($jsonResponse);
    
   // display the text in a JSON format
   var_dump($decodedJson); 
}
catch (Exception $e) {
    // handle exception
}
```

## License

The library is published under the MIT license (see [`LICENSE`](LICENSE) file).