<?php

namespace App\lib;

use NcJoes\OfficeConverter\OfficeConverter;
use Slim\Psr7\Response;

class PdfResponse
{

    private const DIR = __DIR__ . '/../../template/';

    protected  OfficeConverter $converter;

    protected  string $outputFile;

    public function __construct(string $docxFile,string $outputFile)
    {

        $converter = new OfficeConverter($docxFile, self::DIR,'soffice',false);

        $this->outputFile = $outputFile;

        $converter->convertTo($outputFile);
    }

    public function getResponse(string $filename = 'report.pdf'): Response
    {

        $outputFile = $this->outputFile;

        $response =  new \Slim\Psr7\Response();

        $response = $response->withHeader('Content-Type', 'application/pdf');
        $response = $response->withHeader('Content-Disposition', "attachment; filename=$filename");

        $fileStream = fopen(self::DIR . $outputFile, 'r');
        $response->getBody()->write(fread($fileStream, filesize(self::DIR . $outputFile)));
        fclose($fileStream);

        return $response;
    }
}