<?php

namespace App\Controller\Api\Sage;

use App\Controller\Api\Sage\SageController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Service\SageClickUpService;

class InvoicingController extends SageController
{
   /**
     * @Route("/api/sage/invoicing/getEntities/applicationId/{applicationId}/accountPractice/{accountPractice}/companyId/{companyId}/", name="sage_invoicing_get_entities")
     */
    public function getEntities(Request $request){
        $applicationId=( $request->attributes->get('applicationId')) ? $request->attributes->get('applicationId') :'';
        $accountPractice=( $request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') :'';
        $companyId=( $request->attributes->get('companyId')) ? $request->attributes->get('companyId') :'';        
        $resp=$this->getSageService()->getInvoicingEnities($applicationId,$accountPractice,$companyId);
        $response = new Response();
        $response->setContent($resp);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    } 

    /**
     * @Route("/api/sage/invoicing/getCompanies/applicationId/{applicationId}/accountPractice/{accountPractice}/companyId/{companyId}/", name="sage_invoicing_get_companies")
     */
    public function getCompanies(Request $request){
        $applicationId=( $request->attributes->get('applicationId')) ? $request->attributes->get('applicationId') :'';
        $accountPractice=( $request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') :'';
        $companyId=( $request->attributes->get('companyId')) ? $request->attributes->get('companyId') :'';        
        $resp=$this->getSageService()->getInvoicingCompanies($applicationId,$accountPractice,$companyId);
        $response = new Response();
        $response->setContent($resp);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    } 

    /**
     * @Route("/api/sage/invoicing/getActivities/applicationId/{applicationId}/accountPractice/{accountPractice}/companyId/{companyId}/", name="sage_invoicing_get_activities")
     */
    public function getActivities(Request $request){
        $applicationId=( $request->attributes->get('applicationId')) ? $request->attributes->get('applicationId') :'';
        $accountPractice=( $request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') :'';
        $companyId=( $request->attributes->get('companyId')) ? $request->attributes->get('companyId') :'';        
        $resp=$this->getSageService()->getInvoicingActivities($applicationId,$accountPractice,$companyId);
        $response = new Response();
        $response->setContent($resp);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    } 

    /**
     * @Route("/api/sage/invoicing/getEmployees/applicationId/{applicationId}/accountPractice/{accountPractice}/companyId/{companyId}/", name="sage_invoicing_get_employees")
     */
    public function getEmployees(Request $request){
        $applicationId=( $request->attributes->get('applicationId')) ? $request->attributes->get('applicationId') :'';
        $accountPractice=( $request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') :'';
        $companyId=( $request->attributes->get('companyId')) ? $request->attributes->get('companyId') :'';        
        $resp=$this->getSageService()->getInvoicingEmployees($applicationId,$accountPractice,$companyId);
        $response = new Response();
        $response->setContent($resp);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    /**
     * @Route("/api/sage/invoicing/getProjects/applicationId/{applicationId}/accountPractice/{accountPractice}/companyId/{companyId}/", name="sage_invoicing_get_projects")
     */
    public function getProjects(Request $request){
        $applicationId=( $request->attributes->get('applicationId')) ? $request->attributes->get('applicationId') :'';
        $accountPractice=( $request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') :'';
        $companyId=( $request->attributes->get('companyId')) ? $request->attributes->get('companyId') :'';        
        $resp=$this->getSageService()->getInvoicingProjects($applicationId,$accountPractice,$companyId);
        $response = new Response();
        $response->setContent($resp);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    /**
     * @Route("/api/sage/invoicing/createTimeLines/applicationId/{applicationId}/accountPractice/{accountPractice}/companyId/{companyId}/", name="sage_invoicing_get_projects")
     */
    public function createTimeLines(Request $request){
        $applicationId = ($request->attributes->get('applicationId')) ? $request->attributes->get('applicationId') :'';
        $accountPractice = ( $request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') :'';
        $companyId = ( $request->attributes->get('companyId')) ? $request->attributes->get('companyId') :'';    
        $params= json_decode($request->getContent(),true);
        $resp = $this->getSageService()->createTimeLiles($applicationId,$accountPractice,$companyId,$params);
        $response = new Response();
        $response->setContent($resp);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
