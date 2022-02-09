<?php

namespace App\Controller\Api\Sage;

use App\Controller\Api\Sage\SageController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Sage\AccountancyPracticeService;

class AcountancyController extends SageController
{
    private $sageService;
    /**
     * construct function
     *
     * @param AccountancyPracticeService $sageService
     */
    public function __construct(AccountancyPracticeService $sageService)
    {
        $this->sageService = $sageService;
    }

    /**
     * @Route("/api/sage/accountancy/getAccountancyPractices", name="sage_accountancy_practices")
     */
    public function getAccountancyPractices(Request $request)
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
        $resp = $this->sageService->getAccountingPractices($odataStr, $this->getUser());
        return $this->createResponse($resp);
    }
    /**
     * @Route("/api/sage/accountancy/getAccountancyPracticesOption/accountPractice/{accountPractice}", name="sage_options_accountancy_practices")
     */
    public function getAccountancyPracticesOptions(Request $request)
    {
        $accountPractice = ($request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') : '';
        $resp = $this->sageService->getOptionAccountingPractice($accountPractice);
        $response = new Response();
        $response->setContent($resp["content"]);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
