<?php

namespace App\Controller\Api\Sage;

use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Sage\AccountingService;

class AccountingController extends SageController
{
    private $sageService;
    /**
     * construct function
     *
     * @param AccountingService $sageService
     */
    public function __construct(AccountingService $sageService)
    {
        $this->sageService = $sageService;
    }

    /**
     * @Route("/api/sage/accounting/getPeriods/accountPractice/{accountPractice}/companyId/{companyId}", name="sage_accounting_get_periods")
     */
    public function getPeriods(Request $request)
    {
        $odataParams=$request->query->all();
        $odataStr="";
        if(!empty($odataParams)){
            $odataStr="?";
            $i=0;
            foreach($odataParams as $ind=>$val){
                if($i != 0){
                    $odataStr .= "&";
                }
                $odataStr .= $ind."=".$val;
                $i++;
            }
        } 
        $accountPractice = $request->attributes->get('accountPractice', '');
        $companyId = $request->attributes->get('companyId', '');
        $resp = $this->sageService->getPeriods($accountPractice, $companyId,$odataStr);
        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getTradingAccounts/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_trading_accounts")
     */
    public function getTradingAccounts(Request $request)
    {
        $odataParams=$request->query->all();
        $odataStr="";
        if(!empty($odataParams)){
            $odataStr="?";
            $i=0;
            foreach($odataParams as $ind=>$val){
                if($i != 0){
                    $odataStr .= "&";
                }
                $odataStr .= $ind."=".$val;
                $i++;
            }
        } 
        $accountPractice = $request->attributes->get('accountPractice', '');
        $companyId = $request->attributes->get('companyId', '');
        $periodId = $request->attributes->get('periodId');
        $resp = $this->sageService->getTradingAccounts($accountPractice, $companyId, $periodId,$odataStr);
        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/createEntry/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_create_entry")
     */
    public function createEntry(Request $request, FileUploader $fileUploader)
    {
        $accountPractice = $request->attributes->get('accountPractice', '');
        $companyId = $request->attributes->get('companyId', '');
        $periodId = $request->attributes->get('periodId', '');
        $attachement = $request->files->get('attachment');
        $entry = $request->request->get('entry');
        if ($attachement) {
            $statusUploadFile = $fileUploader->upload($attachement);
            if ($statusUploadFile === false) {
                $response = new Response();
                $response->setContent("Error Upload File");
                $response->setStatusCode(Response::HTTP_OK);
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
            $attachement = $this->getParameter("baseUrlApi") . "/" . $statusUploadFile;
        }

        $resp = $this->sageService->createEntry($accountPractice, $companyId, $periodId, $attachement, $entry);

        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getEntries/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_entries")
     */
    public function getEntries(Request $request)
    {
        $odataParams=$request->query->all();
        $odataStr="";
        if(!empty($odataParams)){
            $odataStr="?";
            $i=0;
            foreach($odataParams as $ind=>$val){
                if($i != 0){
                    $odataStr .= "&";
                }
                $odataStr .= $ind."=".$val;
                $i++;
            }
        } 
        $accountPractice = $request->attributes->get('accountPractice', '');
        $companyId = $request->attributes->get('companyId', '');
        $periodId = $request->attributes->get('periodId', '');
        $resp = $this->sageService->getEntries($accountPractice, $companyId, $periodId,$odataStr);
        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getJournals/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_journals")
     */
    public function getJournals(Request $request)
    {
        $odataParams=$request->query->all();
        $odataStr="";
        if(!empty($odataParams)){
            $odataStr="?";
            $i=0;
            foreach($odataParams as $ind=>$val){
                if($i != 0){
                    $odataStr .= "&";
                }
                $odataStr .= $ind."=".$val;
                $i++;
            }
        } 
        $accountPractice = $request->attributes->get('accountPractice', '');
        $companyId = $request->attributes->get('companyId', '');
        $periodId = $request->attributes->get('periodId', '');

        $resp = $this->sageService->getJournals($accountPractice, $companyId, $periodId,$odataStr);

        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getPeriod/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_period")
     */
    public function getPeriod(Request $request)
    {

        $accountPractice = $request->attributes->get('accountPractice', '');
        $companyId = $request->attributes->get('companyId', '');
        $periodId = $request->attributes->get('periodId', '');

        $resp = $this->sageService->getPeriod($accountPractice, $companyId, $periodId);

        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/createTradingAccount/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_create_trading_account")
     */
    public function createTradingAccount(Request $request)
    {
        $accountPractice = $request->attributes->get('accountPractice', '');
        $companyId = $request->attributes->get('companyId', '');
        $periodId = $request->attributes->get('periodId', '');
        $tradingAccount = json_decode($request->getContent(), true);
        $resp = $this->sageService->createTradingAccount($accountPractice, $companyId, $periodId, $tradingAccount);
        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getFinancialAccounts/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_financial_accounts")
     */
    public function getFinancialAccounts(Request $request)
    {
        $odataParams=$request->query->all();
        $odataStr="";
        if(!empty($odataParams)){
            $odataStr="?";
            $i=0;
            foreach($odataParams as $ind=>$val){
                if($i != 0){
                    $odataStr .= "&";
                }
                $odataStr .= $ind."=".$val;
                $i++;
            }
        } 
        $accountPractice = $request->attributes->get('accountPractice', '');
        $companyId = $request->attributes->get('companyId', '');
        $periodId = $request->attributes->get('periodId', '');
        $resp = $this->sageService->getFinancialAccounts($accountPractice, $companyId, $periodId,$odataStr);
        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getCompanyInformation/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_company_information")
     */
    public function getCompanyInformation(Request $request)
    {
        $accountPractice = $request->attributes->get('accountPractice', '');
        $companyId = $request->attributes->get('companyId', '');
        $periodId = $request->attributes->get('periodId', '');
        $resp = $this->sageService->getCompanyInformation($accountPractice, $companyId, $periodId);
        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getTheAnalyticalSections/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_the_analytical_sections")
     */
    public function getTheAnalyticalSections(Request $request)
    {
        $odataParams=$request->query->all();
        $odataStr="";
        if(!empty($odataParams)){
            $odataStr="?";
            $i=0;
            foreach($odataParams as $ind=>$val){
                if($i != 0){
                    $odataStr .= "&";
                }
                $odataStr .= $ind."=".$val;
                $i++;
            }
        } 
        $accountPractice = $request->attributes->get('accountPractice', '');
        $companyId = $request->attributes->get('companyId', '');
        $periodId = $request->attributes->get('periodId', '');

        $resp = $this->sageService->getTheAnalyticalSections($accountPractice, $companyId, $periodId,$odataStr);

        return $this->createResponse($resp);
    }
}
