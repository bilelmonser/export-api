<?php

namespace App\Controller\Api\Sage;

use App\Controller\Api\Sage\SageController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SageClickUpService;
class CompanyController extends SageController
{
    /**
     * @Route("/api/sage/company/getCompanies/accountPractice/{accountPractice}", name="sage_company_get_companies")
     */
    public function getCompanies(Request $request){
        $accountPractice=( $request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') :'5a84d143-5fb1-4fce-bac0-b19ec942231c';
        $resp=$this->getSageService()->getCompanies($accountPractice);
        $response = new Response();
        $response->setContent($resp);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
