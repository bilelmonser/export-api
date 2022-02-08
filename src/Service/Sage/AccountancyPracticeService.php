<?php

namespace App\Service\Sage;

use App\Service\SageClickUpService;
use App\Entity\AccountancyPractice;
use App\Entity\AnalyticalSection;
use App\Entity\SageModel;
use App\Entity\User;
use Exception;

class AccountancyPracticeService extends SageClickUpService
{
    /**
     * get Accounting Practices function
     *
     * @return void
     */
    public function getAccountingPractices(string $odataStr = "")
    {

        $sageModel = $this->ConnectedSageModel;
        $app_id = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/applications/' . $app_id . '/accountancypractices';
        if (!empty($odataStr)) {
            $url .= $odataStr;
        }
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
                    $this->em->getRepository(AccountancyPractice::class)->findBySageModelAll($sageModel)
                );
            } else {
                $this->saveAccountancyPracticesDb(json_decode($result["content"], true));
                $response["content"] = $result["content"];
            }
        }
        return $response;
    }

    /**
     * Get Options For Accounting Practice  function
     *
     * @param String $accountPractice
     * @return void
     */
    public function getOptionAccountingPractice($accountPractice)
    {
        $sageModel = $this->ConnectedSageModel;
        $app_id = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();
        $accountPractice = '5a84d143-5fb1-4fce-bac0-b19ec942231c';
        $url = $this->baseUrlApi . '/accountancypractices/' . $accountPractice . '/applications/' . $app_id . '/options';

        return $this->cltHttpService->execute($url, "GET", [], $tokenAccess);
    }

    /**
     * Save Accountancy Practices function
     *
     * @param array $content
     * @param User $user
     * @return void
     */
    public function saveAccountancyPracticesDb($content)
    {
        $user = $this->ConnectedUser;
        $accountancyPractices = $this->em->getRepository(AccountancyPractice::class)->findBy(["sageModel" => $user->getSageconfigs()->first()]);
        foreach ($content as $val) {
            $existId = false;
            $objUpdated = null;
            foreach ($accountancyPractices as $ind2 => $val2) {
                if ($val["id"] == $val2->getId()) {
                    $existId = true;
                    $objUpdated = $val2;
                    break;
                }
            }
            if ($existId == false) {
                $this->em->persist(
                    (new AccountancyPractice())
                        ->setSageId($val["id"])
                        ->setBusinessId($val["businessId"])
                        ->setName($val["name"])
                        ->setOriginSageApplication($val["originSageApplication"])
                        ->setContactEmail($val["contactEmail"])
                        ->setSageModel($user->getSageconfigs()->first())
                );
            } else {
                $this->em->persist(
                    $objUpdated->setBusinessId($val["businessId"])
                        ->setName($val["name"])
                        ->setOriginSageApplication($val["originSageApplication"])
                        ->setContactEmail($val["contactEmail"])
                        ->setSageModel($user->getSageconfigs()->first())
                );
            }
        }

        $this->em->flush();
    }
}
