<?php

namespace App\Controller\Api\Sage;

use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountingController extends SageController
{
    /**
     * @Route("/api/sage/accounting/getPeriods/accountPractice/{accountPractice}/companyId/{companyId}", name="sage_accounting_get_periods")
     */
    public function getPeriods(Request $request)
    {
        $accountPractice = $request->attributes->get('accountPractice', '');
        $companyId = $request->attributes->get('companyId', '');
        $resp = $this->getSageService()->getPeriods($accountPractice, $companyId);

        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getTradingAccounts/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_trading_accounts")
     */
    public function getTradingAccounts(Request $request)
    {
        $accountPractice = $request->attributes->get('accountPractice', '');
        $companyId = $request->attributes->get('companyId', '');
        $periodId = $request->attributes->get('periodId');
        $resp = $this->getSageService()->getTradingAccounts($accountPractice, $companyId, $periodId);

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
            $attachement = $this->getParameter("baseUrlApi")."/".$statusUploadFile;
        }

        $resp = $this->getSageService()->createEntry($accountPractice, $companyId, $periodId, $attachement, $entry);

        return $this->createResponse($resp);

    }

    /**
     * @Route("/api/sage/accounting/getEntries/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_entries")
     */
    public function getEntries(Request $request)
    {
        $accountPractice = $request->attributes->get('accountPractice', '');
        $companyId = $request->attributes->get('companyId', '');
        $periodId = $request->attributes->get('periodId', '');
        $resp = $this->getSageService()->getEntries($accountPractice, $companyId, $periodId);

        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getJournals", name="sage_accounting_get_journals")
     */
    public function getJournals(Request $request)
    {
        $accountPractice = $request->query->get('accountPractice', '');
        $companyId = $request->query->get('companyId', '');
        $periodId = $request->query->get('periodId', '');

        $resp = $this->getSageService()->getJournals($accountPractice, $companyId, $periodId);

        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getPeriod", name="sage_accounting_get_period")
     */
    public function getPeriod(Request $request)
    {

        $accountPractice = $request->query->get('accountPractice', '');
        $companyId = $request->query->get('companyId', '');
        $periodId = $request->query->get('periodId', '');

        $resp = $this->getSageService()->getPeriod($accountPractice, $companyId, $periodId);

        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/createTradingAccount", name="sage_accounting_create_trading_account")
     */
    public function createTradingAccount(Request $request)
    {
        $accountPractice = $request->attributes->get('accountPractice', '');
        $companyId = $request->attributes->get('companyId', '');
        $periodId = $request->attributes->get('periodId', '');

        $params["name"] = $request->request->get('name', '');
        $params["shortName"] = $request->request->get('shortName', '');
        $params["subsidiaryCollectiveAccountReference"] = $request->request->get('subsidiaryCollectiveAccountReference', '');
        $params["underSurveillance"] = $request->request->get('underSurveillance', '');
        $params["type"] = $request->request->get('type', '');
        $params["\$key"] = $request->request->get('$key', '');
        $params["extras.lettrableAccount"] = $request->request->get('extras.lettrableAccount', '');
        $params["extras.withQuantities"] = $request->request->get('extras.withQuantities', '');
        $params["locked"] = $request->request->get('locked', '');
        $params["cpt1"] = $request->request->get('cpt1', '');
        $params["cpt2"] = $request->request->get('cpt2', '');
        $params["\$uuid"] = $request->request->get('$uuid', '');

        $resp = $this->getSageService()->createTradingAccount($accountPractice, $companyId, $periodId, $params);

        return $this->createResponse($resp);

    }

    /**
     * @Route("/api/sage/accounting/getFinancialAccounts", name="sage_accounting_get_financial_accounts")
     */
    public function getFinancialAccounts(Request $request)
    {
        $accountPractice = $request->query->get('accountPractice', '');
        $companyId = $request->query->get('companyId', '');
        $periodId = $request->query->get('periodId', '');

        $resp = $this->getSageService()->getFinancialAccounts($accountPractice, $companyId, $periodId);

        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getCompanyInformation", name="sage_accounting_get_company_information")
     */
    public function getCompanyInformation(Request $request)
    {
        $accountPractice = $request->query->get('accountPractice', '');
        $companyId = $request->query->get('companyId', '');
        $periodId = $request->query->get('periodId', '');

        $resp = $this->getSageService()->getCompanyInformation($accountPractice, $companyId, $periodId);

        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getTheAnalyticalSections", name="sage_accounting_get_the_analytical_sections")
     */
    public function getTheAnalyticalSections(Request $request)
    {
        $accountPractice = $request->query->get('accountPractice', '');
        $companyId = $request->query->get('companyId', '');
        $periodId = $request->query->get('periodId', '');

        $resp = $this->getSageService()->getTheAnalyticalSections($accountPractice, $companyId, $periodId);

        return $this->createResponse($resp);
    }
}
