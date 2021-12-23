<?php

namespace App\Controller\Api\Sage;

use App\Controller\Api\Sage\SageController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Service\SageClickUpService;

class BatchController extends SageController
{
    /**
     * @Route("/api/sage/batch/createBatch/accountPractice/{accountPractice}/companyId/{companyId}", name="sage_batch_create_batch")
     */
    public function createBatch(SageClickUpService $sageService){
        $resp=$sageService->createBatch();
        $response = new Response();
        $response->setContent($resp);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
