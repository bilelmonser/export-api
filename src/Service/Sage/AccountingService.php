<?php

namespace App\Service\Sage;

use App\Entity\FinancialAccount;
use App\Entity\FinancialPeriod;
use App\Entity\Company;
use App\Entity\Journal;
use App\Entity\CompanyInformation;
use App\Entity\AnalyticalSection;
use App\Service\SageClickUpService;

class AccountingService extends SageClickUpService
{
    /**
     * Get Periods function
     *
     * @param string $accountPractice
     * @param string $companyId
     * @param string $odataStr
     * @return void
     */
    public function getPeriods($accountPractice, $companyId, string $odataStr = "")
    {
        $sageModel = $this->ConnectedSageModel;
        $app_id = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/applications/' . $app_id . '/accountancypractices/' . $accountPractice . '/companies/' . $companyId . '/accounting/periods';
        if (!empty($odataStr)) {
            $url .= $odataStr;
        }
        $company = $this->em->getRepository(Company::class)->findOneBy(["SageId" => $companyId]);
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
                    $this->em->getRepository(FinancialPeriod::class)->findByCompanyAll($company)
                );
            } else {
                if (!empty($company) &&  !empty(json_decode($result["content"], true))) {
                    $this->saveFinancialPeriods(json_decode($result["content"], true), $company);
                }
                $response["content"] = $result["content"];
            }
        }
        return $response;
    }

    /**
     * Save Financial Period functi
     * @param $content
     * @param $company
     */
    public function saveFinancialPeriods($content, $company)
    {
        $financialPeriods = $this->em->getRepository(FinancialPeriod::class)->findBy(["company" => $company]);

        $dateTimeObj = new \DateTime();

        foreach ($content as $val) {
            $existId = false;
            $objUpdated = null;
            foreach ($financialPeriods as $ind2 => $val2) {
                if ($val['$uuid'] == $val2->getUuid()) {
                    $existId = true;
                    $objUpdated = $val2;
                }
            }
            if ($existId == false) {
                $this->em->persist(
                    (new FinancialPeriod())
                        ->setCode($val["code"])
                        ->setFinancialPeriodName($val["financialPeriodName"])
                        ->setStartDate($dateTimeObj->createFromFormat('Y-m-d\TH:i:s', $val["startDate"]))
                        ->setEndDate($dateTimeObj->createFromFormat('Y-m-d\TH:i:s', $val["endDate"]))
                        ->setClosed($val["closed"])
                        ->setExtrasFirstFinancialDate(
                            $dateTimeObj->createFromFormat('Y-m-d\TH:i:s', $val["extras.firstFinancialDate"])
                        )
                        ->setExtrasFiscalEndOfTheFirstFiscalPeriod(
                            $dateTimeObj->createFromFormat('Y-m-d\TH:i:s', $val["extras.fiscalEndOfTheFirstFiscalPeriod"])
                        )
                        ->setExtrasAccountLabelLength($val["extras.accountLabelLength"])
                        ->setExtrasTradingAccountLength($val["extras.tradingAccountLength"])
                        ->setExtrasAccountingLineLabelLength($val["extras.accountingLineLabelLength"])
                        ->setExtrasAccountLength($val["extras.accountLength"])
                        ->setExtrasAuthorizationAlphaAccounts($val["extras.authorizationAlphaAccounts"])
                        ->setExtrasAmountsLength($val["extras.amountsLength"])
                        ->setExtrasWithQuantities($val["extras.withQuantities"])
                        ->setExtrasWithDueDates($val["extras.withDueDates"])
                        ->setExtrasWithMultipleDueDates($val["extras.withMultipleDueDates"])
                        ->setUuid($val['$uuid'])
                        ->setCompany($company)
                );
            } else {
                $this->em->persist(
                    $objUpdated->setCode($val["code"])
                        ->setFinancialPeriodName($val["financialPeriodName"])
                        ->setStartDate($dateTimeObj->createFromFormat('Y-m-d\TH:i:s', $val["startDate"]))
                        ->setEndDate($dateTimeObj->createFromFormat('Y-m-d\TH:i:s', $val["endDate"]))
                        ->setClosed($val["closed"])
                        ->setExtrasFirstFinancialDate(
                            $dateTimeObj->createFromFormat('Y-m-d\TH:i:s', $val["extras.firstFinancialDate"])
                        )
                        ->setExtrasFiscalEndOfTheFirstFiscalPeriod(
                            $dateTimeObj->createFromFormat('Y-m-d\TH:i:s', $val["extras.fiscalEndOfTheFirstFiscalPeriod"])
                        )
                        ->setExtrasAccountLabelLength($val["extras.accountLabelLength"])
                        ->setExtrasTradingAccountLength($val["extras.tradingAccountLength"])
                        ->setExtrasAccountingLineLabelLength($val["extras.accountingLineLabelLength"])
                        ->setExtrasAccountLength($val["extras.accountLength"])
                        ->setExtrasAuthorizationAlphaAccounts($val["extras.authorizationAlphaAccounts"])
                        ->setExtrasAmountsLength($val["extras.amountsLength"])
                        ->setExtrasWithQuantities($val["extras.withQuantities"])
                        ->setExtrasWithDueDates($val["extras.withDueDates"])
                        ->setExtrasWithMultipleDueDates($val["extras.withMultipleDueDates"])
                        ->setCompany($company)
                );
            }
        }

        $this->em->flush();
    }

    /**
     * @param $accountPractice
     * @param $companyId
     * @param $periodId
     * @return mixed|string|null
     */
    public function getPeriod($accountPractice, $companyId, $periodId)
    {
        $sageModel = $this->ConnectedSageModel;
        $app_id = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/applications/' . $app_id . '/accountancypractices/' . $accountPractice . '/companies/' . $companyId . '/accounting/periods/' . $periodId;

        $company = $this->em->getRepository(Company::class)->findOneBy(["SageId" => $companyId]);

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
                    $this->em->getRepository(FinancialPeriod::class)->findOneBy($periodId)
                );
            } else {
                if (!empty(json_decode($result["content"], true))) {
                    $this->addFinancialPeriod(json_decode($result["content"], true), $company);
                }
                $response["content"] = $result["content"];
            }
        }
        return $response;
    }

    /**
     * Save Financial Period functi
     * @param $content
     * @param $company
     */
    public function addFinancialPeriod($content, $company)
    {

        $dateTimeObj = new \DateTime();

        if ($this->em->getRepository(FinancialPeriod::class)->count(["uuid" => $content['$uuid']]) === 0) {

            $fPeriods = new FinancialPeriod();
            $fPeriods->setCode($content["code"])
                ->setFinancialPeriodName($content["financialPeriodName"])
                ->setStartDate($dateTimeObj->createFromFormat('Y-m-d\TH:i:s', $content["startDate"]))
                ->setEndDate($dateTimeObj->createFromFormat('Y-m-d\TH:i:s', $content["endDate"]))
                ->setClosed($content["closed"])
                ->setExtrasFirstFinancialDate(
                    $dateTimeObj->createFromFormat('Y-m-d\TH:i:s', $content["extras.firstFinancialDate"])
                )
                ->setExtrasFiscalEndOfTheFirstFiscalPeriod(
                    $dateTimeObj->createFromFormat('Y-m-d\TH:i:s', $content["extras.fiscalEndOfTheFirstFiscalPeriod"])
                )
                ->setExtrasAccountLabelLength($content["extras.accountLabelLength"])
                ->setExtrasTradingAccountLength($content["extras.tradingAccountLength"])
                ->setExtrasAccountingLineLabelLength($content["extras.accountingLineLabelLength"])
                ->setExtrasAccountLength($content["extras.accountLength"])
                ->setExtrasAuthorizationAlphaAccounts($content["extras.authorizationAlphaAccounts"])
                ->setExtrasAmountsLength($content["extras.amountsLength"])
                ->setExtrasWithQuantities($content["extras.withQuantities"])
                ->setExtrasWithDueDates($content["extras.withDueDates"])
                ->setExtrasWithMultipleDueDates($content["extras.withMultipleDueDates"])
                ->setUuid($content['$uuid'])
                ->setCompany($company);

            $this->em->persist($fPeriods);
            $this->em->flush();
        }
    }

    /**
     * Get Trading Accounts function
     *
     * @param string $accountPractice
     * @param string $companyId
     * @param string $periodId
     * @param string $odataStr
     * @return void
     */
    public function getTradingAccounts(string $accountPractice, string $companyId, string $periodId, string $odataStr = "")
    {
        $sageModel = $this->ConnectedSageModel;
        $appId = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/applications/' . $appId . '/accountancypractices/' . $accountPractice . '/companies/' . $companyId . '/accounting/periods/' . $periodId . '/accounts/trading';
        if (!empty($odataStr)) {
            $url .= $odataStr;
        }
        $result = $this->cltHttpService->execute($url, "GET", [], $tokenAccess);
        $response = [];
        $response["status"] = $result["status"];
        if (($result["status"] == 200) || ($result["status"] == 201)) {
            $response["content"] = $result["content"];
        } else {
            $response["content"] = "error";
        }
        return $response;
    }

    /**
     * Create Entry function
     *
     * @pararn mixed|string
     * @param string $accountPractice
     * @param string $companyId
     * @param string $periodId
     * @param $tradingAccount
     * @return mixed|string
     */
    public function createTradingAccount(
        string $accountPractice,
        string $companyId,
        string $periodId,
        $tradingAccount
    ) {
        $sageModel = $this->ConnectedSageModel;
        $appId = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/applications/' . $appId . '/accountancypractices/' . $accountPractice . '/companies/' . $companyId . '/accounting/periods/' . $periodId . '/accounts/trading';
        $result = $this->cltHttpService->execute($url, "POST", $tradingAccount, $tokenAccess, 1);
        $response = [];
        $response["status"] = $result["status"];
        if (($result["status"] == 200) || ($result["status"] == 201)) {
            $response["content"] = $result["content"];
        } else {
            $response["content"] = "error";
        }
        return $response;
    }

    /**
     * @param string $accountPractice
     * @param $companyId
     * @param $periodId
     * @param $odataStr
     * @return mixed|string|null
     */
    public function getFinancialAccounts(string $accountPractice, $companyId, $periodId, string $odataStr = "")
    {

        $sageModel = $this->ConnectedSageModel;
        $app_id = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/applications/' . $app_id . '/accountancypractices/' . $accountPractice . '/companies/' . $companyId . '/accounting/periods/' . $periodId . '/accounts/financial';
        if (!empty($odataStr)) {
            $url .= $odataStr;
        }
        $company = $this->em->getRepository(Company::class)->findOneBy(["SageId" => $companyId]);
        $period = $this->em->getRepository(FinancialPeriod::class)->findOneBy(["uuid" => $periodId]);
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
                    $this->em->getRepository(FinancialAccount::class)->findBy(["company" => $company, "period" => $period])
                );
            } else {

                if (!count(json_decode($result["content"], true))
                    && $company
                    && $period
                ) {
                    $this->saveFinancialAccounts(json_decode($result["content"], true), $company, $period);
                }
                $response["content"] = $result["content"];
            }
        }
        return $response;
    }

    /**
     * @param array $content
     * @param Company $company
     * @param FinancialPeriod $period
     */
    public function saveFinancialAccounts(array $content, Company $company, FinancialPeriod $period)
    {

        foreach ($content as $val) {

            $this->em->persist(
                (new FinancialAccount())
                    ->setNormalizedTradingAccountType($val['normalizedTradingAccountType'])
                    ->setExtrasCollectiveAccountFrom($val['extras.collectiveAccount.from'])
                    ->setExtrasCollectiveAccountTo($val['extras.collectiveAccount.to'])
                    ->setType($val['type'])
                    ->setFinAccKey($val['$key'])
                    ->setName($val['name'])
                    ->setExtrasLettrableAccount($val['extras.lettrableAccount'])
                    ->setExtrasWithQuantities($val['extras.withQuantities'])
                    ->setLocked($val['locked'])
                    ->setCpt1($val['cpt1'])
                    ->setCpt2($val['cpt2'])
                    ->setUuid($val['$uuid'])
                    ->setCompany($company)
                    ->setPeriod($period)
            );
        }

        $this->em->flush();
    }

    /**
     * @param string $accountPractice
     * @param $companyId
     * @param $periodId
     * @param $odataStr
     * @return mixed|string|null
     */
    public function getJournals(string $accountPractice, $companyId, $periodId, string $odataStr = "")
    {

        $sageModel = $this->ConnectedSageModel;
        $app_id = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();

        $url = $this->baseUrlApi . '/applications/' . $app_id . '/accountancypractices/' . $accountPractice . '/companies/' . $companyId . '/accounting/periods/' . $periodId . '/journals';
        if (!empty($odataStr)) {
            $url .= $odataStr;
        }
        $company = $this->em->getRepository(Company::class)->findOneBy(["SageId" => $companyId]);
        $period = $this->em->getRepository(FinancialPeriod::class)->findOneBy(["uuid" => $periodId]);
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
                    $this->em->getRepository(Journal::class)->findBy(["company" => $company, "period" => $period])
                );
            } else {
                if (!empty(json_decode($result["content"], true)) && !empty($company) && !empty($period)) {
                    $this->saveJournals(json_decode($result["content"], true), $company, $period);
                }
                $response["content"] = $result["content"];
            }
        }
        return $response;
    }

    /**
     * @param array $content
     * @param Company $company
     * @param FinancialPeriod $period
     */
    public function saveJournals(array $content, Company $company, FinancialPeriod $period)
    {
        $this->em->createQuery(
            'DELETE FROM App\Entity\Journal j WHERE j.company = :company and j.period = :period'
        )->setParameter('company', $company)->setParameter('period', $period)->execute();

        foreach ($content as $val) {
            $journal = new Journal();
            $lockEndDate = \DateTime::createFromFormat('Y-m-d\TH:i:s', $val['lockEndDate']);
            $journal->setName($val['name'])
                ->setShortName($val['shortName'])
                ->setOriginalJournalType($val['originalJournalType'])
                ->setNormalizedJournalType($val['normalizedJournalType'])
                ->setAccountingDocumentLength($val['accountingDocumentLength'])
                ->setBankAccount($val['bankAccount'])
                ->setAccountsForbidden(serialize($val['accountsForbidden']))
                ->setWithoutPropagationDate($val['withoutPropagationDate'])
                ->setWithoutPropagationReference($val['withoutPropagationReference'])
                ->setLockEndDate($lockEndDate)
                ->setUuid($val['$uuid'])
                ->setCompany($company)
                ->setPeriod($period);

            $this->em->persist($journal);
        }

        $this->em->flush();
    }

    /**
     * Get Entries function
     *
     * @param string $accountPractice
     * @param string $companyId
     * @param string $periodId
     * @param string $odataStr
     * @return void
     */
    public function getEntries(string $accountPractice, string $companyId, string $periodId, string $odataStr = "")
    {
        $sageModel = $this->ConnectedSageModel;
        $response = [];
        $appId = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/applications/' . $appId . '/accountancypractices/' . $accountPractice . '/companies/' . $companyId . '/accounting/periods/' . $periodId . '/entries';
        if (!empty($odataStr)) {
            $url .= $odataStr;
        }
        $result = $this->cltHttpService->execute($url, "GET", [], $tokenAccess);
        $response["status"] = $result["status"];
        if (($result["status"] == 200) || ($result["status"] == 201)) {
            $response["content"] = $result["content"];
        } else {
            $response["content"] = "error";
        }

        return $response;
    }

    /**
     * Create Entry function
     *
     * @param string $accountPractice
     * @param string $companyId
     * @param string $periodId
     * @param $attachement
     * @param $entry
     * @return mixed|string
     */
    public function createEntry(
        string $accountPractice,
        string $companyId,
        string $periodId,
        $attachement,
        $entry
    ) {

        $sageModel = $this->ConnectedSageModel;
        $appId = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi . '/applications/' . $appId . '/accountancypractices/' . $accountPractice . '/companies/' . $companyId . '/accounting/periods/' . $periodId . '/entries';
        $response = [];
        $params = [];
        $params["entry"] = $entry;
        if (isset($attachment) && !empty($attachment)) {
            $params["attachement"] = $attachement;
        }
        $result = $this->cltHttpService->execute($url, "POST", $params, $tokenAccess, 2);
        $response = [];
        $response["status"] = $result["status"];
        if (($result["status"] == 200) || ($result["status"] == 201)) {
            $response["content"] = $result["content"];
        } else {
            $response["content"] = "error";
        }
        return $response;
    }

    /**
     * @param string $accountPractice
     * @param $companyId
     * @param $periodId
     * @return mixed|string|null
     */
    public function getCompanyInformation(string $accountPractice, $companyId, $periodId)
    {
        $this->getSageModel();
        $sageModel = $this->ConnectedSageModel;
        $app_id = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();

        $url = $this->baseUrlApi . '/applications/' . $app_id . '/accountancypractices/' . $accountPractice . '/companies/' . $companyId . '/accounting/periods/' . $periodId . '/operatingcompany';

        $company = $this->em->getRepository(Company::class)->findOneBy(["SageId" => $companyId]);
        $period = $this->em->getRepository(FinancialPeriod::class)->findOneBy(["uuid" => $periodId]);
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
                    $this->em->getRepository(CompanyInformation::class)->findBy(["company" => $company, "period" => $period])
                );
            } else {
                if (!empty(json_decode($result["content"], true)) && !empty($company) && !empty($period)) {
                    $this->saveCompanyInformation(json_decode($result["content"], true), $company, $period);
                }
                $response["content"] = $result["content"];
            }
        }
        return $response;
    }

    /**
     * @param array $content
     * @param Company $company
     * @param FinancialPeriod $period
     */
    public function saveCompanyInformation(array $content, Company $company, FinancialPeriod $period)
    {
        $this->em->createQuery(
            'DELETE FROM App\Entity\CompanyInformation fa WHERE fa.company = :company and fa.period = :period'
        )->setParameter('company', $company)->setParameter('period', $period)->execute();

        $this->em->persist(
            (new CompanyInformation())
                ->setUuid($content["\$uuid"])
                ->setApe($content['ape'])
                ->setNaf($content['naf'])
                ->setSiret($content['siret'])
                ->setSiren($content['siren'])
                ->setTaxSystem($content['taxSystem'])
                ->setTaxPeriod($content['taxPeriod'])
                ->setFiscalSystem($content['fiscalSystem'])
                ->setFiscalStatus($content['fiscalStatus'])
                ->setCompany($company)
                ->setPeriod($period)
        );

        $this->em->flush();
    }

    /**
     * @param string $accountPractice
     * @param $companyId
     * @param $periodId
     * @param $odataStr
     * @return mixed|string|null
     */
    public function getTheAnalyticalSections(string $accountPractice, $companyId, $periodId, string $odataStr = "")
    {

        $sageModel = $this->ConnectedSageModel;
        $app_id = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();

        $url = $this->baseUrlApi . '/applications/' . $app_id . '/accountancypractices/' . $accountPractice . '/companies/' . $companyId . '/accounting/periods/' . $periodId . '/analytic';
        if (!empty($odataStr)) {
            $url .= $odataStr;
        }
        $company = $this->em->getRepository(Company::class)->findOneBy(["SageId" => $companyId]);
        $period = $this->em->getRepository(FinancialPeriod::class)->findOneBy(["uuid" => $periodId]);
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
                    $this->em->getRepository(AnalyticalSection::class)->findBy(["company" => $company, "period" => $period])
                );
            } else {
                if (!empty(json_decode($result["content"], true)) && !empty($company) && !empty($period)) {
                    $this->saveTheAnalyticalSections(json_decode($result["content"], true), $company, $period);
                }
                $response["content"] = $result["content"];
            }
        }
        return $response;
    }

    /**
     * @param array $content
     * @param Company $company
     * @param FinancialPeriod $period
     */
    public function saveTheAnalyticalSections(array $content, Company $company, FinancialPeriod $period)
    {
        $this->em->createQuery(
            'DELETE FROM App\Entity\CompanyInformation fa WHERE fa.company = :company and fa.period = :period'
        )->setParameter('company', $company)->setParameter('period', $period)->execute();

        foreach ($content as $item) {
            $this->em->persist(
                (new AnalyticalSection())
                    ->setCode($item["code"])
                    ->setLabel($item['label'])
                    ->setAxe($item['axe'])
                    ->setSuperSection($item['superSection'])
                    ->setUuid($item['$uuid'])
                    ->setCompany($company)
                    ->setPeriod($period)
            );
        }

        $this->em->flush();
    }
}
