<?php

namespace App\Controller\Api\Sage;

use App\Controller\Api\Sage\SageController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Sage\CompaniesService;

class CompanyController extends SageController
{
    private $sageService;
    /**
     * construct function
     *
     * @param CompaniesService $sageService
     */
    public function __construct(CompaniesService $sageService)
    {
        $this->sageService = $sageService;
    }
    /**
     * @Route("/api/sage/company/getCompanies/accountPractice/{accountPractice}", name="sage_company_get_companies")
     */
    public function getCompanies(Request $request)
    {
        $accountPractice = ($request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') : '';
        $resp = $this->sageService->getCompanies($accountPractice);
        return $this->createREsponse($resp);
    }
}
