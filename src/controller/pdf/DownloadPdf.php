<?php

namespace App\controller\pdf;

use App\controller\admin\AdminAction;
use App\lib\BudgetReportDocx;
use App\lib\Time;
use NcJoes\OfficeConverter\OfficeConverter;
use Slim\Psr7\Response;
use TCPDF;
use thiagoalessio\TesseractOCR\Tests\Common\TestCase;

class DownloadPdf extends AdminAction
{

    protected function action(): Response
    {
//        $content = $this->getFormData()['content'];
//
//
        $dir = __DIR__ . '/../../../template/';
//
//        $target = $dir . 'test.docx';
//
//
        $data = $this->fundService->getYearlyExpenses(1,Time::getCurrentYear());

        $data['TITLE'] = 'Test';

        $reportContent =[
            $data,
        ];

       $target =  BudgetReportDocx::generate($reportContent);

        $converter = new OfficeConverter($target, $dir,'soffice',false);

        try {
            $converter->convertTo('output-file.pdf');
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return $this->respondWithData($data);

//
//        $stylesheet = "<style>".file_get_contents($dir)." </style>";
//
//        $pdf = new TCPDF();
//
//        $pdf->AddPage();
//
//        $pdf->writeHTML($stylesheet . $content);
//
//        $pdfOutput = $pdf->Output('', 'S');
//
//        $response = new Response();
//
//        $response = $response->withHeader('Content-Type', 'application/pdf');
//        $response = $response->withHeader('Content-Disposition', 'inline; filename="filename.pdf"');
//
//        $response->getBody()->write($pdfOutput);
//
//        return $response;

    }
}