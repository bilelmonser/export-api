<?php

namespace App\Controller\Api\Sage;

use App\Service\FileUploader;
use App\Service\Sage\AccountingService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountingController extends SageController
{

    /**
     * @Route ("/api/sage/accounting/getPeriods/accountPractice/{accountPractice}/companyId/{companyId}", name="sage_accounting_get_periods")
     * @param Request $request
     * @param AccountingService $sageService
     * @return Response
     */
    public function getPeriods(Request $request, AccountingService $sageService)
    {
        $odataParams = $request->query->all();
        $odataStr = "";
        if (!empty($odataParams)) {
            $odataStr = "?";
            $i = 0;
            foreach ($odataParams as $ind => $val) {
                if ($i != 0) {
                    $odataStr .= "&";
                }
                $odataStr .= $ind . "=" . $val;
                $i++;
            }
        }
        $accountPractice = $request->attributes->get('accountPractice', '');
        $companyId = $request->attributes->get('companyId', '');
        $resp = $sageService->getPeriods($accountPractice, $companyId, $odataStr);

        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getTradingAccounts/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_trading_accounts")
     * @param Request $request
     * @param string $accountPractice
     * @param string $companyId
     * @param string $periodId
     * @param AccountingService $sageService
     * @return Response
     */
    public function getTradingAccounts(Request $request, string $accountPractice, string $companyId, string $periodId, AccountingService $sageService): Response
    {
        $odataParams = $request->query->all();
        $odataStr = "";

        if (!empty($odataParams)) {
            $odataStr = "?";
            $i = 0;
            foreach ($odataParams as $ind => $val) {
                if ($i != 0) {
                    $odataStr .= "&";
                }
                $odataStr .= $ind . "=" . $val;
                $i++;
            }
        }
        $resp = $sageService->getTradingAccounts($accountPractice, $companyId, $periodId, $odataStr);

        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/createEntry/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_create_entry")
     */
    public function createEntry(Request $request, $accountPractice, $companyId, $periodId, FileUploader $fileUploader, AccountingService $sageService): Response
    {
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

        $resp = $sageService->createEntry($accountPractice, $companyId, $periodId, $attachement, $entry);

        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getEntries/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_entries")
     * @param Request $request
     * @param AccountingService $sageService
     * @param string $accountPractice
     * @param string $companyId
     * @param string $periodId
     * @return Response
     */
    public function getEntries(Request $request, AccountingService $sageService, string $accountPractice, string $companyId, string $periodId): Response
    {
        $odataParams = $request->query->all();
        $odataStr = "";

        if (!empty($odataParams)) {
            $odataStr = "?";
            $i = 0;

            foreach ($odataParams as $ind => $val) {

                if ($i != 0) {
                    $odataStr .= "&";
                }
                $odataStr .= $ind . "=" . $val;
                $i++;
            }
        }
        $resp = $sageService->getEntries($accountPractice, $companyId, $periodId, $odataStr);

        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getJournals/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_journals")
     * @param Request $request
     * @param string $accountPractice
     * @param string $companyId
     * @param string $periodId
     * @param AccountingService $sageService
     * @return Response
     */
    public function getJournals(Request $request, string $accountPractice, string $companyId, string $periodId, AccountingService $sageService): Response
    {
        $odataParams = $request->query->all();
        $odataStr = "";
        if (!empty($odataParams)) {
            $odataStr = "?";
            $i = 0;
            foreach ($odataParams as $ind => $val) {
                if ($i != 0) {
                    $odataStr .= "&";
                }
                $odataStr .= $ind . "=" . $val;
                $i++;
            }
        }

        $resp = $sageService->getJournals($accountPractice, $companyId, $periodId, $odataStr);

        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getPeriod/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_period")
     * @param Request $request
     * @param string $accountPractice
     * @param string $companyId
     * @param string $periodId
     * @param AccountingService $sageService
     * @return Response
     */
    public function getPeriod(Request $request, string $accountPractice, string $companyId, string $periodId, AccountingService $sageService): Response
    {

        $resp = $sageService->getPeriod($accountPractice, $companyId, $periodId);

        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/createTradingAccount/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_create_trading_account")
     * @param Request $request
     * @param string $accountPractice
     * @param string $companyId
     * @param string $periodId
     * @param AccountingService $sageService
     * @return JsonResponse|Response
     */
    public function createTradingAccount(Request $request, string $accountPractice, string $companyId, string $periodId, AccountingService $sageService)
    {
        $tradingAccount = json_decode($request->getContent(), true);

        $message = $sageService->validateContent($tradingAccount, ['shortName', 'subsidiaryCollectiveAccountReference', 'name']);
        if (null !== $message) {
            return $this->json(
                $message,
                Response::HTTP_BAD_REQUEST);
        }

        $resp = $sageService->createTradingAccount($accountPractice, $companyId, $periodId, $tradingAccount);

        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getFinancialAccounts/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_financial_accounts")
     * @param Request $request
     * @param string $accountPractice
     * @param string $companyId
     * @param string $periodId
     * @param AccountingService $sageService
     * @return Response
     */
    public function getFinancialAccounts(Request $request, string $accountPractice, string $companyId, string $periodId, AccountingService $sageService): Response
    {
        $odataParams = $request->query->all();
        $odataStr = "";

        if (!empty($odataParams)) {
            $odataStr = "?";
            $i = 0;

            foreach ($odataParams as $ind => $val) {

                if ($i != 0) {
                    $odataStr .= "&";
                }
                $odataStr .= $ind . "=" . $val;
                $i++;
            }
        }

        $resp = $sageService->getFinancialAccounts($accountPractice, $companyId, $periodId, $odataStr);

        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/accounting/getCompanyInformation/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_company_information")
     * @param string $accountPractice
     * @param string $companyId
     * @param string $periodId
     * @param AccountingService $sageService
     * @return JsonResponse
     */
    public function getCompanyInformation(string $accountPractice, string $companyId, string $periodId, AccountingService $sageService): JsonResponse
    {
        $resp = $sageService->getCompanyInformation($accountPractice, $companyId, $periodId);

        return $this->json(json_decode($resp["content"]), $resp["status"]);
    }

    /**
     * @Route("/api/sage/accounting/getTheAnalyticalSections/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_the_analytical_sections")
     * @param Request $request
     * @param string $accountPractice
     * @param string $companyId
     * @param string $periodId
     * @param AccountingService $sageService
     * @return Response
     */
    public function getTheAnalyticalSections(Request $request, string $accountPractice, string $companyId, string $periodId, AccountingService $sageService): Response
    {
        $odataParams = $request->query->all();
        $odataStr = "";

        if (!empty($odataParams)) {
            $odataStr = "?";
            $i = 0;

            foreach ($odataParams as $ind => $val) {

                if ($i != 0) {
                    $odataStr .= "&";
                }
                $odataStr .= $ind . "=" . $val;
                $i++;
            }
        }

        $resp = $sageService->getTheAnalyticalSections($accountPractice, $companyId, $periodId, $odataStr);

        return $this->createResponse($resp);
    }
}