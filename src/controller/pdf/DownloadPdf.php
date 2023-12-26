<?php

namespace App\controller\pdf;

use App\controller\admin\AdminAction;
use Slim\Psr7\Response;
use TCPDF;

class DownloadPdf extends AdminAction
{

    protected function action(): Response
    {
        $content = $this->getFormData()['content'];

        $pdf = new TCPDF();

        $pdf->AddPage();

        $pdf->writeHTML($content);

        $pdfOutput = $pdf->Output('', 'S');

        $response = new Response();

        $response = $response->withHeader('Content-Type', 'application/pdf');
        $response = $response->withHeader('Content-Disposition', 'inline; filename="filename.pdf"');

        $response->getBody()->write($pdfOutput);

        return $response;
    }
}