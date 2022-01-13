<?php

namespace App\Service\Sage;

use App\Entity\AccountancyPractice;
use App\Entity\Company;
use App\Service\SageClickUpService;

class CompaniesService extends SageClickUpService
{


    /**
     * get Companies function
     *
     * @param string $accountPractice
     * @param string $odataStr
     * @return mixed|string|null
     */
    public function getCompanies(string $accountPractice, string $odataStr = "")
    {
        $sageModel = $this->ConnectedSageModel;
        $app_id = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/applications/' . $app_id . '/accountancypractices/' . $accountPractice . '/companies';
        if (!empty($odataStr)) {
            $url .= $odataStr;
        }
        $accountPracticeObj = $this->em->getRepository(AccountancyPractice::class)->findOneBy(
            ["SageId" => $accountPractice]
        );
        /********************** */
        $result = $this->cltHttpService->execute($url, "GET", [], $tokenAccess);
        $response = [];
        $response["status"] = $result["status"];
        if (in_array($result["status"], $this::STATUS_ERROR_SERVER)) {
            $response["content"] = $result["content"];
        } else {

            if (in_array($result["status"], $this::STATUS_NOT_FOUND) || in_array(
                $result["status"],
                $this::STATUS_NO_CONTENT
            )) {

                $response["content"] = $this->serializer->serializeContent(
                    $this->em->getRepository(Company::class)->findByAccountancyPracticeAll([$accountPracticeObj])
                );
            } else {
                if (!empty($accountPracticeObj) && (!empty(json_decode($result["content"], true)))) {
                    $this->saveCompanies(json_decode($result["content"], true), $accountPracticeObj);
                }
                $response["content"] = $result["content"];
            }
        }
        return $response;
    }

    /**
     * save Companies function
     *
     * @param array $content
     * @param AccountancyPractice $accountancyPractice
     * @return void
     */
    public function saveCompanies(array $content, AccountancyPractice $accountancyPractice)
    {
        $allSavedCompanies = $this->em->getRepository(Company::class)->findBy(["accountancyPractice" => $accountancyPractice]);
        foreach ($content as $val) {
            $existId = false;
            $objUpdated = null;
            foreach ($allSavedCompanies as $ind2 => $val2) {
                if ($val['id'] == $val2->getId()) {
                    $existId = true;
                    $objUpdated = $val2;
                }
            }
            if ($existId == false) {
                $this->em->persist(
                    (new Company())
                        ->setSageId($val["id"])
                        ->setBusinessId($val["businessId"])
                        ->setName($val["name"])
                        ->setIsAccountancyPractice($val["isAccountancyPractice"])
                        ->setAccountancyPractice($accountancyPractice)
                );
            } else {
                $this->em->persist(
                    $objUpdated->setBusinessId($val["businessId"])
                        ->setName($val["name"])
                        ->setIsAccountancyPractice($val["isAccountancyPractice"])
                        ->setAccountancyPractice($accountancyPractice)
                );
            }
        }

        $this->em->flush();
    }
}
