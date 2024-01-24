<?php

namespace App\controller\user\reciept;

use App\controller\admin\AdminAction;
use App\lib\Time;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use TCPDF;

class ReceiptMaker extends AdminAction
{

    protected function action(): Response
    {

        $receiptId = $this->args['id'];

        try {

            $transaction = $this->transactionService->findById($receiptId);

            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            // Do not print the header line
            $pdf->SetPrintHeader(false);

            // Add a page
            $pdf->AddPage();

            $pdf->SetFont('times', 'B', 16);

            $pdf->Image('./resources/logo.jpeg', 10, 10, 20);
            $pdf->Cell(0, 10, 'Carissa Homes Subdivision Phase 7', 0, 1, 'C', false); // Add 'false' for no border
            $pdf->Cell(0, 10, 'Monthly Dues Invoice', 0, 1, 'C', false); // Add 'false

            $pdf->SetFont('times', '', 12);

            $transactionNumber = $transaction->getId();
            $homeownerName = $transaction->getUser()->getName();
            $property = "B" . $transaction->getUser()->getBlock() . " L" . $transaction->getUser()->getLot();

            $amount = $transaction->getAmount();
            $paymentDate = Time::convertDateTimeToDateString($transaction->getCreatedAt());
            $coverage = $transaction->getFromMonth() . ' - ' . $transaction->getToMonth();

            $pdf->Cell(0, 10, 'Transaction Number: ' . $transactionNumber, 0, 1);
            $pdf->Cell(0, 10, 'Homeowner: ' . $homeownerName, 0, 1);
            $pdf->Cell(0, 10, 'Property: ' . $property, 0, 1);
            $pdf->Cell(0, 10, 'Amount: ' . $amount, 0, 1);
            $pdf->Cell(0, 10, 'Payment Date: ' . $paymentDate, 0, 1);
            $pdf->Cell(0, 10, 'Coverage: ' . $coverage, 0, 1);

            $pdf->Ln(10); // Add some vertical spacing
            $pdf->MultiCell(0, 10, 'This invoice serves as proof that the payment has been made.', 0, 'L');

            $pdfContent = $pdf->Output('', 'S');

            $response = new \Slim\Psr7\Response();

            $response = $response->withHeader('Content-Type', 'application/pdf');
            $response = $response->withHeader('Content-Disposition', 'attachment; filename="report.pdf"');

            $response->getBody()->write($pdfContent);

            return $response;

        } catch (Exception $e) {
            return $this->respondWithData(['message' => $e->getMessage()]);
        }
    }

}