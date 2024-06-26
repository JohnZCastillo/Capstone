<?php

declare(strict_types=1);

namespace App\controller\admin;

use App\controller\Action;
use App\exception\fund\FundNotFound;
use App\lib\DocxMaker;
use App\lib\Login;
use App\lib\LoginDetails;
use App\lib\NumberFormat;
use App\lib\PdfResponse;
use App\model\budget\IncomeModel;
use App\model\enum\LogsTag;
use App\model\LoginHistoryModel;
use App\model\LogsModel;
use App\model\ReceiptModel;
use App\model\TransactionModel;
use App\model\UserModel;
use App\service\AnnouncementHistoryService;
use App\service\AnnouncementService;
use App\service\AreaService;
use App\service\BillService;
use App\service\CodeModelService;
use App\service\DuesService;
use App\service\ExpenseService;
use App\service\FundService;
use App\service\FundSourceService;
use App\service\IncomeService;
use App\service\IssueMessageService;
use App\service\IssuesService;
use App\service\LoginHistoryService;
use App\service\LogsService;
use App\service\OverviewService;
use App\service\PaymentService;
use App\service\PriviligesService;
use App\service\ProjectService;
use App\service\ReceiptService;
use App\service\SystemSettingService;
use App\service\TransactionLogsService;
use App\service\TransactionService;
use App\service\UserLogsService;
use App\service\UserService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use Slim\Flash\Messages;
use Slim\Psr7\Response;

abstract class AdminAction extends Action
{
    protected UserService $userService;
    protected PaymentService $paymentService;
    protected TransactionService $transactionService;
    protected DuesService $duesService;
    protected ReceiptService $receiptService;
    protected Messages $flashMessage;

    protected LogsService $logsService;

    protected TransactionLogsService $transactionLogsService;

    protected IssuesService $issuesService;

    protected AnnouncementService $announcementService;

    protected AnnouncementHistoryService $announcementHistoryService;

    protected SystemSettingService $systemSettingService;
    protected LoginHistoryService $loginHistoryService;
    protected PriviligesService $privilegesService;

    protected BillService $billService;

    protected FundService $fundService;

    protected FundSourceService $fundSourceService;

    protected  ExpenseService $expenseService;

    protected  IncomeService $incomeService;

    protected  CodeModelService $codeModelService;

    protected UserLogsService $userLogsService;

    protected  AreaService $areaService;

    protected OverviewService $overviewService;

    protected  IssueMessageService $issueMessageService;

    protected  ProjectService $projectService;

    /**
     * @param UserService $userService
     * @param PaymentService $paymentService
     * @param TransactionService $transactionService
     * @param DuesService $duesService
     * @param ReceiptService $receiptService
     * @param Messages $flashMessage
     * @param LogsService $logsService
     * @param TransactionLogsService $transactionLogsService
     * @param IssuesService $issuesService
     * @param AnnouncementService $announcementService
     * @param AnnouncementHistoryService $announcementHistoryService
     * @param SystemSettingService $systemSettingService
     * @param LoginHistoryService $loginHistoryService
     * @param PriviligesService $privilegesService
     * @param BillService $billService
     * @param FundService $fundService
     * @param FundSourceService $fundSourceService
     * @param ExpenseService $expenseService
     * @param IncomeService $incomeService
     * @param CodeModelService $codeModelService
     * @param UserLogsService $userLogsService
     * @param AreaService $areaService
     * @param OverviewService $overviewService
     * @param IssueMessageService $issueMessageService
     */
    public function __construct(UserService $userService, PaymentService $paymentService, TransactionService $transactionService, DuesService $duesService, ReceiptService $receiptService, Messages $flashMessage, LogsService $logsService, TransactionLogsService $transactionLogsService, IssuesService $issuesService, AnnouncementService $announcementService, AnnouncementHistoryService $announcementHistoryService, SystemSettingService $systemSettingService, LoginHistoryService $loginHistoryService, PriviligesService $privilegesService, BillService $billService, FundService $fundService, FundSourceService $fundSourceService, ExpenseService $expenseService, IncomeService $incomeService, CodeModelService $codeModelService, UserLogsService $userLogsService, AreaService $areaService, OverviewService $overviewService, IssueMessageService $issueMessageService, ProjectService $projectService)
    {
        $this->userService = $userService;
        $this->paymentService = $paymentService;
        $this->transactionService = $transactionService;
        $this->duesService = $duesService;
        $this->receiptService = $receiptService;
        $this->flashMessage = $flashMessage;
        $this->logsService = $logsService;
        $this->transactionLogsService = $transactionLogsService;
        $this->issuesService = $issuesService;
        $this->announcementService = $announcementService;
        $this->announcementHistoryService = $announcementHistoryService;
        $this->systemSettingService = $systemSettingService;
        $this->loginHistoryService = $loginHistoryService;
        $this->privilegesService = $privilegesService;
        $this->billService = $billService;
        $this->fundService = $fundService;
        $this->fundSourceService = $fundSourceService;
        $this->expenseService = $expenseService;
        $this->incomeService = $incomeService;
        $this->codeModelService = $codeModelService;
        $this->userLogsService = $userLogsService;
        $this->areaService = $areaService;
        $this->overviewService = $overviewService;
        $this->issueMessageService = $issueMessageService;
        $this->projectService = $projectService;
    }

    protected function addErrorMessage($message)
    {
        $this->flashMessage->addMessage('errorMessage', $message);
    }

    protected function addSuccessMessage($message)
    {
        $this->flashMessage->addMessage('successMessage', $message);
    }

    protected function addMessage($key, $message)
    {
        $this->flashMessage->addMessage($key, $message);
    }

    protected function getLoginUser(): UserModel
    {
        return $this->userService->findById(Login::getLogin());
    }

    protected function addActionLog(string $message, string $tag): void
    {
        $actionLog = new LogsModel();
        $actionLog->setAction($message);
        $actionLog->setTag($tag);
        $actionLog->setUser($this->getLoginUser());
        $actionLog->setCreatedAt(new DateTime());
        $this->logsService->addLog($actionLog);
    }

    protected function logLoginHistory(): void
    {
        $user = $this->getLoginUser();
        $loginHistoryModel = new LoginHistoryModel();
        $loginDetails = LoginDetails::getLoginDetails();
        $loginHistoryModel->setLoginDate($loginDetails['loginTime']);
        $loginHistoryModel->setIp($loginDetails['ipAddress']);
        $loginHistoryModel->setDevice($loginDetails['deviceLogin']);
        $loginHistoryModel->setSession($loginDetails['sessionId']);
        $loginHistoryModel->setUser($user);

        $this->loginHistoryService->addLoginLog($loginHistoryModel);
    }

    protected function getCollectionStartDate(): DateTime
    {

        $settings = $this->paymentService->findById(1);

        $start = Carbon::createFromDate($settings->getStart());

        return $start->toDateTime();

    }
    /**
     * @throws FundNotFound
     */
    protected function setupIncome(TransactionModel $transaction): void
    {

        $months = CarbonPeriod::create($transaction->getFromMonth(), '1 month', $transaction->getToMonth());

        foreach ($months as $month) {

            $date = Carbon::createFromFormat('Y-m-d', $month->format('Y-m-d'));
            $date->setDay(1);

            $income = new IncomeModel();

            $income->setTitle('Monthly Payment');
            $income->setAmount($this->duesService->getDue($date->format('Y-m-d')));
            $income->setSource($this->fundSourceService->findById(1));
            $income->setFund($this->fundService->findById(1));
            $income->setCreatedAt($date->toDateTime());
            $income->setTransaction($transaction);

            $this->incomeService->save($income);
        }

    }

    public function  generateReceipt(TransactionModel $transaction): Response
    {

        $user = $transaction->getUser();

        $receipts = $transaction->getReceipts();

        $receiptsHolder = '';

        /** @var ReceiptModel $receipt */
        foreach ($receipts as $receipt) {

            $receiptsHolder = $receiptsHolder . ' ' . $receipt->getTransaction()->getPaymentMethod();

            if ($receipt->getTransaction()->getPaymentMethod() == 'gcash') {
                $receiptsHolder = $receiptsHolder . ' (' . $receipt->getReferenceNumber() . ')';
            }
        }

        $fromCoverage = $transaction->getFromMonth();
        $toCoverage = $transaction->getToMonth();

        $coverage = $fromCoverage->format('M Y') . ' - ' . $toCoverage->format('M Y');

        $amount = $transaction->getAmount();

        NumberFormat::format($amount);

        $docxMaker = new DocxMaker('sales_invoice.docx');

        $docxMaker->addHeader([
            'REPORT_COVERAGE' => $this->coverage,
            'ID' => $transaction->getId(),
            'UNIT' => 'B' . $user->getBlock() . ' L' . $user->getLot(),
            'USER' => $this->areaService->getOwner($transaction->getUser()),
            'AMOUNT' => $amount,
            'ADMIN' => $transaction->getProcessBy()->getName(),
            'COVERAGE' => $coverage,
            'DATE' => $transaction->getCreatedAt()->format('Y-m-d'),
            'PAYMENT' => $transaction->getPaymentMethod()
        ]);

        $output = $docxMaker->output();

        $pdfResponse = new PdfResponse($output, 'test.pdf');

        $filename = "sales-invoice-" . $fromCoverage->format('m-Y') . '-to-' . $toCoverage->format('m-Y') . '.pdf';

        return $pdfResponse->getResponse($filename);
    }

}