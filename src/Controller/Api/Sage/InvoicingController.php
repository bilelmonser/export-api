<?php

namespace App\Controller\Api\Sage;

use App\Controller\Api\Sage\SageController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Service\Sage\InvoicingService;

class InvoicingController extends SageController
{
    private $sageService;
    /**
     * construct function
     *
     * @param InvoicingService $sageService
     */
    public function __construct(InvoicingService $sageService)
    {
        $this->sageService = $sageService;
    }
    /**
     * @Route("/api/sage/invoicing/getEntities/applicationId/{applicationId}/accountPractice/{accountPractice}/companyId/{companyId}/", name="sage_invoicing_get_entities")
     */
    public function getEntities(Request $request)
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
        $applicationId = ($request->attributes->get('applicationId')) ? $request->attributes->get('applicationId') : '';
        $accountPractice = ($request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') : '';
        $companyId = ($request->attributes->get('companyId')) ? $request->attributes->get('companyId') : '';
        $resp = $this->sageService->getInvoicingEnities($applicationId, $accountPractice, $companyId, $odataStr);
        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/invoicing/getCompanies/applicationId/{applicationId}/accountPractice/{accountPractice}/companyId/{companyId}/", name="sage_invoicing_get_companies")
     */
    public function getCompanies(Request $request)
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
        $applicationId = ($request->attributes->get('applicationId')) ? $request->attributes->get('applicationId') : '';
        $accountPractice = ($request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') : '';
        $companyId = ($request->attributes->get('companyId')) ? $request->attributes->get('companyId') : '';
        $resp = $this->sageService->getInvoicingCompanies($applicationId, $accountPractice, $companyId, $odataStr);
        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/invoicing/getActivities/applicationId/{applicationId}/accountPractice/{accountPractice}/companyId/{companyId}/", name="sage_invoicing_get_activities")
     */
    public function getActivities(Request $request)
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
        $applicationId = ($request->attributes->get('applicationId')) ? $request->attributes->get('applicationId') : '';
        $accountPractice = ($request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') : '';
        $companyId = ($request->attributes->get('companyId')) ? $request->attributes->get('companyId') : '';
        $resp = $this->sageService->getInvoicingActivities($applicationId, $accountPractice, $companyId, $odataStr);
        return $this->createResponse($resp);
    }

    /**
     * @Route("/api/sage/invoicing/getEmployees/applicationId/{applicationId}/accountPractice/{accountPractice}/companyId/{companyId}/", name="sage_invoicing_get_employees")
     */
    public function getEmployees(Request $request)
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
        $applicationId = ($request->attributes->get('applicationId')) ? $request->attributes->get('applicationId') : '';
        $accountPractice = ($request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') : '';
        $companyId = ($request->attributes->get('companyId')) ? $request->attributes->get('companyId') : '';
        $resp = $this->sageService->getInvoicingEmployees($applicationId, $accountPractice, $companyId, $odataStr);
        return $this->createResponse($resp);
    }
    /**
     * @Route("/api/sage/invoicing/getProjects/applicationId/{applicationId}/accountPractice/{accountPractice}/companyId/{companyId}/", name="sage_invoicing_get_projects")
     */
    public function getProjects(Request $request)
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
        $applicationId = ($request->attributes->get('applicationId')) ? $request->attributes->get('applicationId') : '';
        $accountPractice = ($request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') : '';
        $companyId = ($request->attributes->get('companyId')) ? $request->attributes->get('companyId') : '';
        $resp = $this->sageService->getInvoicingProjects($applicationId, $accountPractice, $companyId, $odataStr);
        return $this->createResponse($resp);
    }
    /**
     * @Route("/api/sage/invoicing/createTimeLines/applicationId/{applicationId}/accountPractice/{accountPractice}/companyId/{companyId}/", name="sage_invoicing_create_time_lines")
     */
    public function createTimeLines(Request $request)
    {
        $applicationId = ($request->attributes->get('applicationId')) ? $request->attributes->get('applicationId') : '';
        $accountPractice = ($request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') : '';
        $companyId = ($request->attributes->get('companyId')) ? $request->attributes->get('companyId') : '';
        $params = json_decode($request->getContent(), true);
        $resp = $this->sageService->createTimeLiles($applicationId, $accountPractice, $companyId, $params);
        return $this->createResponse($resp);
    }
}
