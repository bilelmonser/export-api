<?php

namespace App\Controller\Api\Sage;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Service\SageClickUpService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Service\FileUploader;

class AccountingController extends SageController
{
    /**
     * @Route("/api/sage/accounting/getPeriods/accountPractice/{accountPractice}/companyId/{companyId}", name="sage_accounting_get_periods")
     */
    public function getPeriods(Request $request){
        $accountPractice=( $request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') :'5a84d143-5fb1-4fce-bac0-b19ec942231c';
        $companyId=( $request->attributes->get('companyId')) ? $request->attributes->get('companyId') :'22df8495-6357-44b2-8ea0-05272756d1da';
        $resp=$this->getSageService()->getPeriods($accountPractice,$companyId);
        $response = new Response();
        $response->setContent($resp);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    /**
     * @Route("/api/sage/accounting/getTradingAccounts/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_trading_accounts")
     */
    public function getTradingAccounts(Request $request){
        $accountPractice=( $request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') :'5a84d143-5fb1-4fce-bac0-b19ec942231c';
        $companyId=( $request->attributes->get('companyId')) ? $request->attributes->get('companyId') :'22df8495-6357-44b2-8ea0-05272756d1da';
        $periodId=( $request->attributes->get('periodId')) ? $request->attributes->get('periodId') :'b6ecf76f-c23b-4f7d-9cd9-cb2e7ccff35f';
        $resp=$this->getSageService()->getTradingAccounts($accountPractice,$companyId,$periodId);
        $response = new Response();
        $response->setContent($resp["content"]);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    /**
     * @Route("/api/sage/accounting/createEntry/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_create_entry")
     */
    public function createEntry(Request $request,FileUploader $fileUploader){
        $accountPractice=( $request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') :'5a84d143-5fb1-4fce-bac0-b19ec942231c';
        $companyId=( $request->attributes->get('companyId')) ? $request->attributes->get('companyId') :'22df8495-6357-44b2-8ea0-05272756d1da';
        $periodId=( $request->attributes->get('periodId')) ? $request->attributes->get('periodId') :'b6ecf76f-c23b-4f7d-9cd9-cb2e7ccff35f';
        $attachement=$request->files->get('attachment');
        $entry=$request->request->get('entry');        
        if ($attachement) {
            $statusUploadFile=$fileUploader->upload($attachement);
            if($statusUploadFile === false){
                $response = new Response();
                $response->setContent("Error Upload File");
                $response->setStatusCode(Response::HTTP_OK);
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
             $attachement= $this->getParameter("baseUrlApi")."/".$statusUploadFile;
        }
        
        $resp=$this->getSageService()->createEntry($accountPractice,$companyId,$periodId,$attachement,$entry);
        $response = new Response();
        $response->setContent($resp["content"]);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
        
    }
    /**
     * @Route("/api/sage/accounting/getEntries/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_get_entries")
     */
    public function getEntries(Request $request){
        $accountPractice=( $request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') :'5a84d143-5fb1-4fce-bac0-b19ec942231c';
        $companyId=( $request->attributes->get('companyId')) ? $request->attributes->get('companyId') :'22df8495-6357-44b2-8ea0-05272756d1da';
        $periodId=( $request->attributes->get('periodId')) ? $request->attributes->get('periodId') :'b6ecf76f-c23b-4f7d-9cd9-cb2e7ccff35f';
        $resp=$this->getSageService()->getEntries($accountPractice,$companyId,$periodId);
        $response = new Response();
        $response->setContent($resp["content"]);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
