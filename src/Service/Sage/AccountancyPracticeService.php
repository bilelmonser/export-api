<?php

namespace App\Service\Sage;

use App\Service\App\SerializeService;
use App\Service\SageClickUpService;
use App\Entity\AccountancyPractice;
use App\Entity\AnalyticalSection;
use App\Entity\SageModel;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class AccountancyPracticeService extends SageClickUpService
{

    /**
     * get Accounting Practices function
     *
     * @return void
     */
    public function getAccountingPractices(User $user, string $odataStr = "") : array
    {
        $this->getSageModel($user);
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
                $this->saveAccountancyPracticesDb(json_decode($result["content"], true), $user);
                $response["content"] = $result["content"];
            }
        }

        return $response;
    }

    /**
     * Get Options For Accounting Practice  function
     *
     * @param String $accountPractice
     * @return array|void
     */
    public function getOptionAccountingPractice(string $accountPractice)
    {
        $sageModel = $this->ConnectedSageModel;
        $app_id = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/accountancypractices/' . $accountPractice . '/applications/' . $app_id . '/options';

        return $this->cltHttpService->execute($url, "GET", [], $tokenAccess);
    }

    /**
     * @param array $content
     * @param User|null $user
     */
    public function saveAccountancyPracticesDb(array $content, ?User $user)
    {
        /** @var AccountancyPractice[] $accountancyPractices */
        $accountancyPractices = $this->em->getRepository(AccountancyPractice::class)->findBy(["sageModel" => $user->getSageconfigs()->first()]);

        $accountancyPracticesTmp = $accountancyPractices;

        foreach ($content as $val) {
            $existId = false;
            $objUpdated = null;

            foreach ($accountancyPracticesTmp as $ind2 => $val2) {

                if ($val["id"] == $val2->getSageId()) {
                    $existId = true;
                    $objUpdated = $val2;
                    unset($accountancyPracticesTmp[$ind2]);
                    break;
                }
            }

            $accountancyPractice = !$existId ? (new AccountancyPractice())
                ->setSageId($val["id"])
                ->setBusinessId($val["businessId"])
                ->setName($val["name"])
                ->setOriginSageApplication($val["originSageApplication"])
                ->setContactEmail($val["contactEmail"])
                ->setSageModel($user->getSageconfigs()->first())
                :
                $objUpdated
                    ->setBusinessId($val["businessId"])
                    ->setName($val["name"])
                    ->setOriginSageApplication($val["originSageApplication"])
                    ->setContactEmail($val["contactEmail"])
                    ->setSageModel($user->getSageconfigs()->first());

            $this->em->persist($accountancyPractice);
        }

        $this->em->flush();
    }
}
