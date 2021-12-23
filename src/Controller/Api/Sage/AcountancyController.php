<?php

namespace App\Controller\Api\Sage;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Controller\Api\Sage\SageController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Service\SageClickUpService;

class AcountancyController extends SageController
{
    /**
     * @Route("/api/sage/accountancy/getAccountancyPractices", name="sage_accountancy_practices")
     */
    public function getAccountancyPractices()
    {
        $resp=$this->getSageService()->getAccountingPractices();
        $response = new Response();
        $response->setContent($resp);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }	
	/**
     * @Route("/api/sage/accountancy/getAccountancyPracticesOption/accountPractice/{accountPractice}", name="sage_options_accountancy_practices")
     */
    public function getAccountancyPracticesOptions(Request $request)
    {
		$accountPractice=( $request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') :'5a84d143-5fb1-4fce-bac0-b19ec942231c';
		$resp=$this->getSageService()->getOptionAccountingPractice($accountPractice);
        $response = new Response();
        $response->setContent($resp["content"]);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
