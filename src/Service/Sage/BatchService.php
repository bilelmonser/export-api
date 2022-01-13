<?php

namespace App\Service\Sage;

use App\Service\SageClickUpService;

class BatchService extends SageClickUpService
{
    /**
     * Create Batch function
     *
     * @param string $applicationId
     * @param string $accountPractice
     * @param string $companyId
     * @return void
     */
    public function createBatch(string $applicationId, string $accountPractice, string $companyId)
    {
        $sageModel = $this->ConnectedSageModel;
        $app_id = (!empty($sageModel->getAppId())) ? $sageModel->getAppId() : $applicationId;
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/applications/'.$applicationId.'/accountancypractices/'.$accountPractice.'/companies/'.$companyId.'/queues/in/batches';
        $result = $this->cltHttpService->execute($url, "POST", [], $tokenAccess);
        $response = [];
        $result = $this->cltHttpService->execute($url, "POST", [], $tokenAccess, 2);
        if (($result["status"] == 200) || ($result["status"] == 201)) {
            $response["content"] = $result["content"];
        } else {
            $response["content"] = "error";
        }
        return $response;
    }
}
