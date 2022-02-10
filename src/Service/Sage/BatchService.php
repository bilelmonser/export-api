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
     * @return array
     */
    public function createBatch(string $applicationId, string $accountPractice, string $companyId, $batchData): array
    {

        $sageModel = $this->ConnectedSageModel;
        $app_id = (!empty($sageModel->getAppId())) ? $sageModel->getAppId() : $applicationId;
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/applications/'.$app_id.'/accountancypractices/'.$accountPractice.'/companies/'.$companyId.'/queues/in/batches';
        $response = [];
        $result = $this->cltHttpService->execute($url, "POST", json_decode($batchData, true), $tokenAccess, 2);

        $response["status"] = $result["status"];

        if (in_array($result["status"], [200, 201])) {
            $response["content"] = $result["content"];
        } else {
            $response["content"] = "error";
        }

        return $response;
    }
}
