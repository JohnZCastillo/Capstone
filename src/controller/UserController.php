<?php

namespace App\controller;

use App\exception\AlreadyPaidException;
use App\exception\date\InvalidDateRange;
use App\exception\image\ImageNotGcashReceiptException;
use App\exception\image\UnsupportedImageException;
use App\exception\NotUniqueReferenceException;
use App\lib\Currency;
use App\lib\Filter;
use App\lib\GCashReceiptValidator;
use App\lib\Image;
use App\lib\Login;
use App\lib\ReferenceExtractor;
use App\lib\Time;
use App\model\enum\IssuesStatus;
use App\model\IssuesModel;
use App\model\LogsModel;
use App\model\TransactionModel;
use DateTime;
use Slim\Views\Twig;
use TCPDF;

class UserController extends Controller
{

    public function home($request, $response, $args)
    {

        // Get the user
        $user = $this->getLogin();

        $view = Twig::fromRequest($request);
        $queryParams = $request->getQueryParams();

        $filter = Filter::check($queryParams);

        $page = $queryParams['page'] ?? 1;
        $query = empty($queryParams['query']) ? null : $queryParams['query'];

        $errorMessage = $this->flashMessages->getFirstMessage("ErrorMessage");
        $welcomeMessage = $this->flashMessages->getFirstMessage("welcome");

        // Set max transactions per page
        $max = 4;

        // Get transactions
        $paginator = $this->transactionService->getAll($page, $max, $query, $filter, $user);

        // Get balances
        $currentMonth = Time::thisMonth();
        $nextMonth = Time::nextMonth();
        $currentDue = $this->getBalance($currentMonth);
        $nextDue = $this->getBalance($nextMonth);

        // Calculate total dues
        $totalDues = $this->getTotalDues();

        // Prepare data for the view
        $data = [
            'errorMessage' => $errorMessage,
            'currentMonth' => $currentMonth,
            'nextMonth' => $nextMonth,
            'currentDue' => Currency::format($currentDue),
            'nextDue' => Currency::format($nextDue),
            'unpaid' => Currency::format($totalDues),
            'transactions' => $paginator->getItems(),
            'currentPage' => $page,
            'query' => $query,
            'from' => Time::toMonth($filter['from']),
            'to' => Time::toMonth($filter['to']),
            'status' => $filter['status'],
            'settings' => $this->getPaymentSettings(),
            'paginator' => $paginator,
            'welcomeMessage' => $welcomeMessage
        ];

        return $view->render($response, 'pages/user-home.html', $data);
    }

    public function manageIssue($request, $response, $args)
    {

        $id = $args['id'];

        $view = Twig::fromRequest($request);

        //might throw and error
        $issue = $this->issuesService->findById($id);

        return $view->render($response, 'pages/user-manage-issue.html', [
            'issue' => $issue,
        ]);
    }

    public function receipt($request, $response, $args)
    {

        $transactionId = $args['id'];

        $transaction = $this->transactionService->findById($transactionId);


        // Create a new TCPDF instance
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
        $amount = $transaction->getAmount();
        $paymentDate = Time::convertDateTimeToDateString($transaction->getCreatedAt());
        $coverage = $transaction->getFromMonth() .  ' - ' . $transaction->getToMonth();

        $pdf->Cell(0, 10, 'Transaction Number: ' . $transactionNumber, 0, 1);
        $pdf->Cell(0, 10, 'Homeowner: ' . $homeownerName, 0, 1);
        $pdf->Cell(0, 10, 'Amount: ' . $amount, 0, 1);
        $pdf->Cell(0, 10, 'Payment Date: ' . $paymentDate, 0, 1);
        $pdf->Cell(0, 10, 'Coverage: ' . $coverage, 0, 1);

        $pdf->Ln(10); // Add some vertical spacing
        $pdf->MultiCell(0, 10, 'This invoice serves as proof that the payment has been made.', 0, 'L');

        $pdfContent = $pdf->Output('', 'S');

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="monthly_dues_receipt.pdf"');
        header('Content-Length: ' . strlen($pdfContent));

        echo $pdfContent;

//        return $response;
    }

    /**
     * View unpaid monthly dues and its total.
     */
    public function dues($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        // login in user
        $user = $this->getLogin();

        // Default payment settings is 1
        $paymentSettings = $this->getPaymentSettings();

        //get arrays of unpaid monhts
        $data = $this->transactionService->getUnpaid($user, $this->duesService, $paymentSettings);

        //since the dues in unpaid month are float.
        //then format it to have peso value / curreny
        $items = Currency::formatArray($data['items'], 'due');

        return $view->render($response, 'pages/user-dues-breakdown.html', [
            'items' => $items,
            'total' => Currency::format($data['total'])
        ]);
    }


    public function transaction($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        // login in user
        $user = $this->getLogin();

        //get transction from databse base on ID
        $transaction = $this->transactionService->findById($args['id']);

        //Default Payment Settings
        $paymentSettings = $this->paymentService->findById(1);

        //Transactions logs
        $logs = $transaction->getLogs();

        $target = $this->issuesService->findByTarget($transaction->getId());

        return $view->render($response, 'pages/user-transaction.html', [
            'transaction' => $transaction,
            'receipts' => $transaction->getReceipts(),
            'logs' => $logs,
            'target' => $target,
        ]);
    }


    /**
     * View unpaid monthly dues and its total.
     */
    public function announcements($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        $queryParams = $request->getQueryParams();

        // if page is present then set value to page otherwise to 1
        $page = isset($queryParams['page']) ? $queryParams['page'] : 1;

        // max transaction per page
        $max = 5;

        $filter = Filter::check($queryParams);

        $result = $this->announcementService->getAll($page, $max, null, $filter);

        return $view->render($response, 'pages/user-announcement.html', [
            'announcements' => $result['announcements'],
            'currentPage' => $page,
            'from' => isset($queryParams['from']) ? $queryParams['from'] : null,
            'to' => isset($queryParams['to']) ? $queryParams['to'] : null,
            'status' => isset($queryParams['status']) ? $queryParams['status'] : null,
            'totalPages' => ceil(($result['totalAnnouncement']) / $max),
        ]);
    }

    /**
     * View unpaid monthly dues and its total.
     */
    public function accountSettings($request, $response, $args)
    {


        $user = $this->getLogin();
        $name = $user->getName();
        $email = $user->getEmail();
        $block = $user->getBlock();
        $lot = $user->getLot();

        $loginHistory = $this->loginHistoryService->getLogs($user);
        $currentSession = session_id();

        $view = Twig::fromRequest($request);

        return $view->render($response, 'pages/user-account-settings.html', [
            "loginHistory" => $loginHistory,
            "sessionId" => $currentSession,
            "name" => $name,
            "email" => $email,
            "block" => $block,
            "lot" => $lot,
            "user" => $user,
            "logs" => $user->getMyLogs(),
        ]);
    }


    /**
     * View Issues.
     */
    public function issues($request, $response, $args)
    {

        $message = $this->flashMessages->getFirstMessage('message');

        $view = Twig::fromRequest($request);

        $queryParams = $request->getQueryParams();

        // if page is present then set value to page otherwise to 1
        $page = isset($queryParams['page']) ? $queryParams['page'] : 1;

        $type = isset($queryParams['type']) ? $queryParams['type'] : 'posted';

        // max transaction per page
        $max = 5;

        $filter = Filter::check($queryParams);

        $user = $this->getLogin();

        $pagination = $this->issuesService->getAll($page, $max, null, $filter, $user, $type);

        return $view->render($response, 'pages/user-all-issues.html', [
            'type' => $type,
            'message' => $message,
            'issues' => $pagination->getItems(),
            'currentPage' => $page,
            'from' => $queryParams['from'] ?? null,
            'to' => $queryParams['to'] ?? null,
            'status' => $queryParams['status'] ?? null,
            'pagination' => $pagination
        ]);
    }

    /**
     * Create an issues
     */
    public function issue($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        $queryParams = $request->getQueryParams();

        $issue = new IssuesModel();

        $anonymous = $request->getParsedBody()['anonymous'];

        $issue->setTitle($request->getParsedBody()['title']);
        $issue->setContent($request->getParsedBody()['content']);
        $issue->setCreatedAt(Time::timestamp());
        $issue->setStatus(IssuesStatus::pending());
        $issue->setAction('None');
        $issue->setUser($this->getLogin());
        $issue->setType('posted');
        $issue->setTarget($request->getParsedBody()['target']);

        if ($anonymous) {
            $issue->setUser(null);
            $this->flashMessages->addMessage('message', "Anonymous  Issues Sent");
        }

        $this->issuesService->save($issue);

        return $response
            ->withHeader('Location', "/issues")
            ->withStatus(302);
    }

    public function archiveIssue($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        $id = $args['id'];

        $issue = $this->issuesService->findById($id);

        $issue->setType('archive');

        $this->issuesService->save($issue);

        return $response
            ->withHeader('Location', "/issues")
            ->withStatus(302);
    }

    public function unArchiveIssue($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        $id = $args['id'];

        $issue = $this->issuesService->findById($id);

        $issue->setType('posted');

        $this->issuesService->save($issue);

        return $response
            ->withHeader('Location', "/issues")
            ->withStatus(302);
    }
}