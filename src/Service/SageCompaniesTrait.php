<?php

namespace App\Service;

use App\Entity\AccountancyPractice;
use App\Entity\Company;

trait SageCompaniesTrait
{


    /**
     * get Companies function
     *
     * @param string $accountPractice
     * @return mixed|string|null
     */
    public function getCompanies(string $accountPractice)
    {
        $sageModel = $this->ConnectedSageModel;
        $app_id = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi.'/applications/'.$app_id.'/accountancypractices/'.$accountPractice.'/companies';
        $accountPracticeObj = $this->em->getRepository(AccountancyPractice::class)->findOneBy(
            ["SageId" => $accountPractice]
        );

        return $this->saveAndGetDataByEntity(
            $url,
            $tokenAccess,
            'saveCompanies',
            [$accountPracticeObj],
            Company::class,
            'findByAccountancyPracticeAll',
            $accountPracticeObj
        );
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
        $companySageIdList = $this->em->getRepository(Company::class)->findCompanySageIdList($accountancyPractice);

        if (!empty($content)) {

            foreach ($content as $val) {

                if (!in_array(["SageId" => $val["id"]], $companySageIdList)) {

                    $this->em->persist(
                        (new Company())
                            ->setSageId($val["id"])
                            ->setBusinessId($val["businessId"])
                            ->setName($val["name"])
                            ->setIsAccountancyPractice($val["isAccountancyPractice"])
                            ->setAccountancyPractice($accountancyPractice)
                    );
                }
            }

            $this->em->flush();
        }
    }
}