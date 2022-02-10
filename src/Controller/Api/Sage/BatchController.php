<?php

namespace App\Controller\Api\Sage;

use App\Controller\Api\Sage\SageController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SageClickUpService;
use App\Service\Sage\BatchService;

class BatchController extends SageController
{
    private $sageService;
    /**
     * construct function
     *
     * @param BatchService $sageService
     */
    public function __construct(BatchService $sageService)
    {
        $this->sageService = $sageService;
    }

    /**
     * @Route("/api/sage/batch/createBatch/applicationId/{applicationId}/accountPractice/{accountPractice}/companyId/{companyId}", name="sage_batch_create_batch")
     */
    public function createBatch(Request $request)
    {
        $applicationId = ($request->attributes->get('applicationId')) ? $request->attributes->get('applicationId') : '';
        $accountPractice = ($request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') : '';
        $companyId = ($request->attributes->get('companyId')) ? $request->attributes->get('companyId') : '';
        $resp = $this->sageService->createBatch($applicationId, $accountPractice, $companyId);
        return $this->createResponse($resp);
    }
}
