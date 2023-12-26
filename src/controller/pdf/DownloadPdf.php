<?php

namespace App\controller\pdf;

use App\controller\admin\AdminAction;
use Slim\Psr7\Response;
use TCPDF;
use thiagoalessio\TesseractOCR\Tests\Common\TestCase;

class DownloadPdf extends AdminAction
{

    protected function action(): Response
    {
        $content = $this->getFormData()['content'];


        $dir =  __DIR__ . '/../../../public/resources/css/theme-default.css';

        $stylesheet = "<style>".file_get_contents($dir)." </style>";

        $pdf = new TCPDF();

        $pdf->AddPage();

        $pdf->writeHTML($stylesheet . $content);

        $pdfOutput = $pdf->Output('', 'S');

        $response = new Response();

        $response = $response->withHeader('Content-Type', 'application/pdf');
        $response = $response->withHeader('Content-Disposition', 'inline; filename="filename.pdf"');

        $response->getBody()->write($pdfOutput);

        return $response;

    }
}