<?php

namespace App\Controller\Api\Sage;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SageController extends AbstractController
{
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
