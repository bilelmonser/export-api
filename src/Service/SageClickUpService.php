<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\SageModel;
use App\Entity\AccountancyPractice;
use App\Entity\Company;
use App\Entity\FinancialPeriod;
use App\Service\ClientHttpService;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Dotenv\Dotenv;
use App\Service\App\SerializeService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;

class SageClickUpService
{
    private $em;
    private $cltHttpService;
    private $ConnectedUser;
    private $ConnectedSageModel;
    private $security;
    private $statusNotFound=['400','401','402','403','404','415'];
    private $statusErrorServer=['500','501','503'];
    private $baseUrlApi;
    private $serializer;
    private $accountancyPractice;
    protected $requestStack;
    private $log;
    /**
     * ClientHttpService constructor.
     *
     */
    public function __construct(EntityManagerInterface $em,ClientHttpService $cltHttpService,Security $security,SerializeService $serializeService,$baseUrlSageApi,RequestStack $requestStack,LoggerInterface $logger)
    {
        $this->em=$em;
        $this->requestStack = $requestStack;
        $request = $this->requestStack->getCurrentRequest();
        $this->accountancyPractice=( $request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') :'';
        $this->cltHttpService=$cltHttpService;
        $this->security = $security;
        //$this->loginSage();
        $this->loginSageByAccountancyPractice();
        $this->serializer = $serializeService;
        $this->baseUrlApi = $baseUrlSageApi;
        $this->log=$logger;

    }
    /**
     * get Accounting Practices function
     *
     * @return void
     */
    public function getAccountingPractices(){
        $sageModel=$this->ConnectedSageModel;
        $app_id=$sageModel->getAppId();
        $tokenAccess=$sageModel->getToken();
        $url=$this->baseUrlApi.'/applications/'.$app_id.'/accountancypractices';
        $result=$this->cltHttpService->execute($url,"GET",[],$tokenAccess);
        $em=$this->em;
        if(in_array($result["status"],$this->statusNotFound)){
            return $result["content"];
        }else if(in_array($result["status"],$this->statusErrorServer)){
            $listLocaly=$em->getRepository(AccountancyPractice::class)->findBySageModelAll($sageModel);
            return $this->serializer->SerializeContent($listLocaly);
        }else{
            $this->saveAccountancyPractices(json_decode($result["content"],true),$em,$user);
            return $result["content"];
        }
    }
    /**
     * Get Options For Accounting Practice  function
     *
     * @param String $accountPractice
     * @return void
     */
    public function getOptionAccountingPractice($accountPractice){
        $sageModel=$this->ConnectedSageModel;
        $app_id=$sageModel->getAppId();
        $tokenAccess=$sageModel->getToken();
        $accountPractice='5a84d143-5fb1-4fce-bac0-b19ec942231c';
        $url=$this->baseUrlApi . '/accountancypractices/' . $accountPractice . '/applications/' . $app_id . '/options';
        return $this->cltHttpService->execute($url,"GET",[],$tokenAccess);
    }
    /**
     * Get Periods function
     *
     * @param string $accountPractice
     * @param string $companyId
     * @return void
     */
    public function getPeriods($accountPractice,$companyId)
    {       
        $sageModel=$this->ConnectedSageModel;
        $app_id=$sageModel->getAppId();
        $tokenAccess=$sageModel->getToken();
        $url=$this->baseUrlApi.'/applications/'.$app_id.'/accountancypractices/'.$accountPractice.'/companies/'.$companyId.'/accounting/periods';
        $result = $this->cltHttpService->execute($url,"GET",[],$tokenAccess);
        $em=$this->em;
        $company = $em->getRepository(Company::class)->findOneBy(["SageId"=>$companyId]);
        
        if(in_array($result["status"],$this->statusNotFound)){
            return $result["content"];
        }else if(in_array($result["status"],$this->statusErrorServer)){
            $listLocaly=$em->getRepository(FinancialPeriod::class)->findByCompanyAll($company);
            return $this->serializer->SerializeContent($listLocaly);
        }else{
            $this->saveFinancialPeriods(json_decode($result["content"],true),$em,$company);
            return $result["content"];
        }
    }
    /**
     * Get Trading Accounts function
     *
     * @param string $accountPractice
     * @param string $companyId
     * @param string $periodId
     * @return void
     */
    public function getTradingAccounts(string $accountPractice,string $companyId,string $periodId){
        $user = $this->ConnectedUser;
        $appId=$sageModel->getAppId();
        $tokenAccess=$sageModel->getToken();
        $url=$this->baseUrlApi.'/applications/'.$appId.'/accountancypractices/'.$accountPractice.'/companies/'.$companyId.'/accounting/periods/'.$periodId.'/accounts/trading';
        return $this->cltHttpService->execute($url,"GET",[],$tokenAccess);

    }
    /**
     * Create Entry function
     *
     * @param string $accountPractice
     * @param string $companyId
     * @param string $periodId
     * @param array $attachement
     * @param array $entry
     * @return void
     */
    public function createEntry(string $accountPractice,string $companyId,string $periodId,$attachement,$entry)
    {
        $sageModel=$this->ConnectedSageModel;
        $appId=$sageModel->getAppId();
        $tokenAccess=$sageModel->getToken();
        $url=$this->baseUrlApi.'/applications/'.$appId.'/accountancypractices/'.$accountPractice.'/companies/'.$companyId.'/accounting/periods/'.$periodId.'/entries';
        $response=[];
        $params=[];
        $params["entry"]=$entry;
        if(isset($attachment) && !empty($attachment)){
            $params["attachement"]=$attachement;
        }
        $result=$this->cltHttpService->execute($url,"POST",$params,$tokenAccess,2);
        

        
        if(in_array($result["status"],$this->statusNotFound)){
            $response["content"]="ko";
        }else{
            $response["content"]=$result["content"];
        }
        return $response;
    }
    /**
     * Get Entries function
     *
     * @param string $accountPractice
     * @param string $companyId
     * @param string $periodId
     * @return void
     */
    public function getEntries(string $accountPractice,string $companyId,string $periodId)
    {       
        $sageModel=$this->ConnectedSageModel;
        $appId=$sageModel->getAppId();
        $tokenAccess=$sageModel->getToken();
        $url=$this->baseUrlApi.'/applications/'.$appId.'/accountancypractices/'.$accountPractice.'/companies/'.$companyId.'/accounting/periods/'.$periodId.'/entries';
        return $this->cltHttpService->execute($url,"GET",[],$tokenAccess);
    }
	/**
     * get Companies function
     *
     * @param string $accountPractice
     * @return void
     */
	public function getCompanies(string $accountPractice){
        $sageModel=$this->ConnectedSageModel;
        $app_id=$sageModel->getAppId();
        $tokenAccess=$sageModel->getToken();
        $url=$this->baseUrlApi.'/applications/'.$app_id.'/accountancypractices/'.$accountPractice.'/companies';
        $result= $this->cltHttpService->execute($url,"GET",[],$tokenAccess);
        $em=$this->em;
        $accountPracticeObj=$em->getRepository(AccountancyPractice::class)->findOneBy(["SageId"=>$accountPractice]);
        
        if(in_array($result["status"],$this->statusNotFound)){
            return $result["content"];
        }else if(in_array($result["status"],$this->statusErrorServer)){
            $listLocaly=$em->getRepository(Company::class)->findByAccountancyPracticeAll($accountPracticeObj);
            return $this->serializer->SerializeContent($listLocaly);
        }else{
            $this->saveCompanies(json_decode($result["content"],true),$em,$accountPracticeObj);
            return $result["content"];
        }
	}
	/**
     * Create Batch function
     *
     * @return void
     */
    public function createBatch()
    {
        $sageModel=$this->ConnectedSageModel;
        $app_id=$sageModel->getAppId();
        $tokenAccess=$sageModel->getToken();
		$accountPractice='5a84d143-5fb1-4fce-bac0-b19ec942231c';
		$companyId='22df8495-6357-44b2-8ea0-05272756d1da';
        $url=$this->baseUrlApi.'/applications/{applicationId}/accountancypractices/'.$accountPractice.'/companies/'.$companyId.'/queues/in/batches';
        return $this->cltHttpService->execute($url,"POST",[],$tokenAccess);
    }
    /**
     * Login Sage ClickUp function
     *
     * @return void
     */
    private function loginSage(){
        $user=$this->security->getUser();
        $em = $this->em;
        $sageModel= $user->getSageconfigs()->first();
        $today = date("Y-m-d H:i:s");
        $dateExpired = $sageModel->getExpiredtoken()->format('Y-m-d H:i:s');
        if(empty($sageModel->getToken()) || (!empty($sageModel->getToken()) && ( $today > $dateExpired ))){
            $url_auth=$user->getSageconfigs()->first()->getUrlAuth();
            $grant_type=$user->getSageconfigs()->first()->getGrantType();
            $client_id=$user->getSageconfigs()->first()->getClientId();
            $client_secret=$user->getSageconfigs()->first()->getClientSecret();
            $audience=$user->getSageconfigs()->first()->getAudience();
            $response=$this->cltHttpService->execute($url_auth,"POST",
            [
                "grant_type"=>$grant_type,
                "client_id"=>$client_id,
                "client_secret"=>$client_secret,
                "audience"=>$audience
            ],"",1);
            $response["content"]=json_decode($response["content"],true);
            if(isset($response["content"]["access_token"])){
                $now = new \DateTime();
                $now->add(new \DateInterval('PT'.$response["content"]["expires_in"].'S'));
                $sageModel->setToken($response["content"]["access_token"]);
                $sageModel->setExpiredToken($now);
                $em->persist($sageModel);
                $em->flush();
            }else{
                return false;
            }
            $this->ConnectedUser=$em->getRepository(User::class)->findOneBy(['email' => 'admin2@admin.com']);
        }else{
            $this->ConnectedUser = $user;
        }
        
    }
    /**
     * Login Sage ClickUp function
     *
     * @return void
     */
    private function loginSageByAccountancyPractice(){
        $em = $this->em;
        $sageModel=$em->getRepository(SageModel::class)->findOneBy(['AccountancyPractice' => $this->accountancyPractice]);
        
        $today = date("Y-m-d H:i:s");
        $dateExpired = $sageModel->getExpiredtoken()->format('Y-m-d H:i:s');
        if(empty($sageModel->getToken()) || (!empty($sageModel->getToken()) && ( $today > $dateExpired ))){
            $url_auth=$sageModel->getUrlAuth();
            $grant_type=$sageModel->getGrantType();
            $client_id=$sageModel->getClientId();
            $client_secret=$sageModel->getClientSecret();
            $audience=$sageModel->getAudience();
            $response=$this->cltHttpService->execute($url_auth,"POST",
            [
                "grant_type"=>$grant_type,
                "client_id"=>$client_id,
                "client_secret"=>$client_secret,
                "audience"=>$audience
            ],"",1);
            $response["content"]=json_decode($response["content"],true);
            if(isset($response["content"]["access_token"])){
                $now = new \DateTime();
                $now->add(new \DateInterval('PT'.$response["content"]["expires_in"].'S'));
                $sageModel->setToken($response["content"]["access_token"]);
                $sageModel->setExpiredToken($now);
                $em->persist($sageModel);
                $em->flush();
            }else{
                return false;
            }
            $this->ConnectedSageModel=$em->getRepository(SageModel::class)->findOneBy(['AccountancyPractice' => $this->accountancyPractice]);
        }else{
            $this->ConnectedSageModel = $sageModel;
        }
        
    }
     /**
     * Save Accountancy Practices function
     *
     * @param Array $content
     * @param EntityManager $em
     * @param User $user
     * @return void
     */
    public function saveAccountancyPractices(array $content,EntityManager $em,User $user){
        $query = $em->createQuery(
            'DELETE FROM App\Entity\AccountancyPractice e WHERE e.sageModel = :id_sage_model'
         )->setParameter('id_sage_model', $user->getSageconfigs()->first())->execute();
        if(!empty($content)){
            foreach($content as $ind=>$val){
                $accountancyPractice = new AccountancyPractice();
                $accountancyPractice->setSageId($val["id"]);
                $accountancyPractice->setBusinessId($val["businessId"]);
                $accountancyPractice->setName($val["name"]);
                $accountancyPractice->setOriginSageApplication($val["originSageApplication"]);
                $accountancyPractice->setContactEmail($val["contactEmail"]);
                $accountancyPractice->setSageModel($user->getSageconfigs()->first());
                $em->persist($accountancyPractice);
              
            }
            $em->flush();
        }        
    }
    /**
     * save Companies function
     *
     * @param Array $content
     * @param EntityManager $em
     * @param AccountancyPractice $accountancyPractice
     * @return void
     */
    public function saveCompanies(array $content,EntityManager $em,AccountancyPractice $accountancyPractice){
        $query = $em->createQuery(
            'DELETE FROM App\Entity\Company a WHERE a.accountancyPractice = :accountancy_practice'
         )->setParameter('accountancy_practice', $accountancyPractice)->execute();
        if(!empty($content)){
            foreach($content as $ind=>$val){
                $company = new Company();
                $company->setSageId($val["id"]);
                $company->setBusinessId($val["businessId"]);
                $company->setName($val["name"]);                
                $company->setIsAccountancyPractice($val["isAccountancyPractice"]);
                $company->setAccountancyPractice($accountancyPractice);
                $em->persist($company);
                
            }
            $em->flush();
        }        
    }
    /**
     * Save Financial Period function
     *
     * @param [type] $content
     * @param [type] $em
     * @param [type] $company
     * @return void
     */
    public function saveFinancialPeriods($content,$em,$company){
        $query = $em->createQuery(
            'DELETE FROM App\Entity\FinancialPeriod f WHERE f.company = :company'
         )->setParameter('company', $company)->execute();
         $dateTimeObj=new \DateTime();
        if(!empty($content)){
            foreach($content as $ind=>$val){
                $fPeriods = new FinancialPeriod();            
                $fPeriods->setCode($val["code"]);
                $fPeriods->setFinancialPeriodName($val["financialPeriodName"]);
                $dateTimeObj->createFromFormat('Y-m-dTH:i:s', $val["startDate"]);
                $fPeriods->setStartDate($dateTimeObj);
                $dateTimeObj->createFromFormat('Y-m-dTH:i:s', $val["endDate"]);
                $fPeriods->setEndDate($dateTimeObj);                
                $fPeriods->setClosed($val["closed"]);
                $dateTimeObj->createFromFormat('Y-m-dTH:i:s', $val["extras.firstFinancialDate"]);
                $fPeriods->setExtrasFirstFinancialDate($dateTimeObj);
                $dateTimeObj->createFromFormat('Y-m-dTH:i:s', $val["extras.fiscalEndOfTheFirstFiscalPeriod"]);
                $fPeriods->setExtrasFiscalEndOfTheFirstFiscalPeriod($dateTimeObj);
                $fPeriods->setExtrasAccountLabelLength($val["extras.accountLabelLength"]);
                $fPeriods->setExtrasTradingAccountLength($val["extras.tradingAccountLength"]);                
                $fPeriods->setExtrasAccountingLineLabelLength($val["extras.accountingLineLabelLength"]);
                $fPeriods->setExtrasAccountLength($val["extras.accountLength"]);
                $fPeriods->setExtrasAuthorizationAlphaAccounts($val["extras.authorizationAlphaAccounts"]);
                $fPeriods->setExtrasAmountsLength($val["extras.amountsLength"]);
                $fPeriods->setExtrasWithQuantities($val["extras.withQuantities"]);                
                $fPeriods->setExtrasWithDueDates($val["extras.withDueDates"]);
                $fPeriods->setExtrasWithMultipleDueDates($val["extras.withMultipleDueDates"]);
                $fPeriods->setUuid($val['$uuid']);
                $fPeriods->setCompany($company);
                $em->persist($fPeriods);
                }
            $em->flush();
        }        
    }

}