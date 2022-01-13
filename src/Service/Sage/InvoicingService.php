<?php

namespace App\Service\Sage;

use App\Service\SageClickUpService;

class InvoicingService extends SageClickUpService
{
    /**
     * get Invoicing Entities function
     *
     * @param string $applicationId
     * @param string $accountPractice
     * @param string $companyId
     * @param string $odataStr
     * @return void
     */
    public function getInvoicingEnities(string $applicationId, string $accountPractice, string $companyId, string $odataStr = "")
    {
        $sageModel = $this->ConnectedSageModel;
        $app_id = (!empty($sageModel->getAppId())) ? $sageModel->getAppId() : $applicationId;
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/applications/' . $app_id . '/accountancypractices/' . $accountPractice . '/companies/' . $companyId . '/invoicing/entities';
        if (!empty($odataStr)) {
            $url .= $odataStr;
        }
        $result = $this->cltHttpService->execute($url, "GET", [], $tokenAccess);

        $response["status"] =  $result["status"];
        if (($result["status"] == 200) || ($result["status"] == 201) || ($result["status"] == 204)) {
            $response["content"] = $result["content"];
        } else {
            $response["content"] = "error";
        }
        return $response;
    }
    /**
     * get Invoicing Companies function
     *
     * @param string $applicationId
     * @param string $accountPractice
     * @param string $companyId
     * @param string $odataStr
     * @return void
     */
    public function getInvoicingCompanies(string $applicationId, string $accountPractice, string $companyId ,string $odataStr = "")
    {
        $sageModel = $this->ConnectedSageModel;
        $app_id = (!empty($sageModel->getAppId())) ? $sageModel->getAppId() : $applicationId;
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/applications/' . $app_id . '/accountancypractices/' . $accountPractice . '/companies/' . $companyId . '/invoicing/companies';
        if (!empty($odataStr)) {
            $url .= $odataStr;
        }
        $result = $this->cltHttpService->execute($url, "GET", [], $tokenAccess);
        $response["status"] =  $result["status"];
        if (($result["status"] == 200) || ($result["status"] == 201)) {
            $response["content"] = $result["content"];
        } else {
            $response["content"] = "error";
        }
        return $response;
    }
    /**
     * get Invoicing Activities function
     *
     * @param string $applicationId
     * @param string $accountPractice
     * @param string $companyId
     * @param string $odataStr
     * @return void
     */
    public function getInvoicingActivities(string $applicationId, string $accountPractice, string $companyId ,string $odataStr = "")
    {
        $sageModel = $this->ConnectedSageModel;
        $app_id = (!empty($sageModel->getAppId())) ? $sageModel->getAppId() : $applicationId;
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/applications/' . $app_id . '/accountancypractices/' . $accountPractice . '/companies/' . $companyId . '/invoicing/activities';
        if (!empty($odataStr)) {
            $url .= $odataStr;
        }
        $result = $this->cltHttpService->execute($url, "GET", [], $tokenAccess);
        $response["status"] =  $result["status"];
        if (($result["status"] == 200) || ($result["status"] == 201)) {
            $response["content"] = $result["content"];
        } else {
            $response["content"] = "error";
        }
        return $response;
    }
    /**
     * get Invoicing Employees function
     *
     * @param string $applicationId
     * @param string $accountPractice
     * @param string $companyId
     * @param string $odataStr
     * @return void
     */
    public function getInvoicingEmployees(string $applicationId, string $accountPractice, string $companyId ,string $odataStr = "")
    {
        $sageModel = $this->ConnectedSageModel;
        $app_id = (!empty($sageModel->getAppId())) ? $sageModel->getAppId() : $applicationId;
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/applications/' . $app_id . '/accountancypractices/' . $accountPractice . '/companies/' . $companyId . '/invoicing/employees';
        if (!empty($odataStr)) {
            $url .= $odataStr;
        }
        $result = $this->cltHttpService->execute($url, "GET", [], $tokenAccess);
        $response["status"] =  $result["status"];
        if (($result["status"] == 200) || ($result["status"] == 201)) {
            $response["content"] = $result["content"];
        } else {
            $response["content"] = "error";
        }
        return $response;
    }
    /**
     * get Invoicing Projects function
     *
     * @param string $applicationId
     * @param string $accountPractice
     * @param string $companyId
     * @param string $odataStr
     * @return void
     */
    public function getInvoicingProjects(string $applicationId, string $accountPractice, string $companyId ,string $odataStr = "")
    {
        $sageModel = $this->ConnectedSageModel;
        $app_id = (!empty($sageModel->getAppId())) ? $sageModel->getAppId() : $applicationId;
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/applications/' . $app_id . '/accountancypractices/' . $accountPractice . '/companies/' . $companyId . '/invoicing/projects';
        if (!empty($odataStr)) {
            $url .= $odataStr;
        }
        $result = $this->cltHttpService->execute($url, "GET", [], $tokenAccess);
        $response["status"] =  $result["status"];
        if (($result["status"] == 200) || ($result["status"] == 201)) {
            $response["content"] = $result["content"];
        } else {
            $response["content"] = "error";
        }
        return $response;
    }
    /**
     *  create Time Lines function
     *
     * @param string $applicationId
     * @param string $accountPractice
     * @param string $companyId
     * @param array $params
     * @return void
     */
    public function createTimeLiles(string $applicationId, string $accountPractice, string $companyId, $params)
    {
        $sageModel = $this->ConnectedSageModel;
        $appId = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/applications/' . $appId . '/accountancypractices/' . $accountPractice . '/companies/' . $companyId . '/invoicing/timelines';
        $response = [];
        $result = $this->cltHttpService->execute($url, "POST", $params, $tokenAccess, 2);
        if (($result["status"] == 200) || ($result["status"] == 201)) {
            $response["content"] = $result["content"];
        } else {
            $response["content"] = "error";
        }
        return $response;
    }
}
