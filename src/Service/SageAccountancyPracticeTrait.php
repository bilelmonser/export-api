<?php

namespace App\Service;

use App\Entity\AccountancyPractice;
use App\Entity\AnalyticalSection;
use App\Entity\SageModel;
use App\Entity\User;
use Exception;

trait SageAccountancyPracticeTrait
{


    /**
     * get Accounting Practices function
     *
     * @return void
     */
    public function getAccountingPractices()
    {
        $sageModel = $this->ConnectedSageModel;
        $app_id = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi.'/applications/'.$app_id.'/accountancypractices';

        return $this->saveAndGetDataByEntity(
            $url,
            $tokenAccess,
            'saveAccountancyPractices',
            [$this->ConnectedUser],
            AnalyticalSection::class,
            'findBySageModelAll',
            $sageModel
        );
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
        $url = $this->baseUrlApi.'/accountancypractices/'.$accountPractice.'/applications/'.$app_id.'/options';

        return $this->cltHttpService->execute($url, "GET", [], $tokenAccess);
    }

    /**
     * Save Accountancy Practices function
     *
     * @param array $content
     * @param User $user
     * @return void
     */
    public function saveAccountancyPractices(array $content, User $user)
    {
        $this->em->createQuery(
            'DELETE FROM App\Entity\AccountancyPractice e WHERE e.sageModel = :id_sage_model'
        )->setParameter('id_sage_model', $user->getSageconfigs()->first())->execute();

        foreach ($content as $val) {

            $this->em->persist(

                (new AccountancyPractice())
                    ->setSageId($val["id"])
                    ->setBusinessId($val["businessId"])
                    ->setName($val["name"])
                    ->setOriginSageApplication($val["originSageApplication"])
                    ->setContactEmail($val["contactEmail"])
                    ->setSageModel($user->getSageconfigs()->first())
            );
        }

        $this->em->flush();
    }
}