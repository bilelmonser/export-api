<?php

namespace App\Controller\Api\Sage;

use App\Service\SageClickUpService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SageController extends AbstractController
{
	private $sageService;
	/**
	 * construct function
	 *
	 * @param SageClickUpService $sageService
	 */
    public function __construct(SageClickUpService $sageService){
		$this->sageService=$sageService;    
	}
	/**
	 * get Sage Service function
	 *
	 * @return SageClickUpService
     */
	public function getSageService(): SageClickUpService
    {
		return $this->sageService;
	}

    /**
     * @param $resp
     * @return Response
     */
    public function createResponse($resp): Response
    {
        $response = new Response();
		$response->setStatusCode($resp["status"]);
        $response->setContent($resp["content"]);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
} 
