<?php

namespace App\Service;

use App\Service\App\SerializeService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class SageClickUpService
{

    use SageLoginTrait;
    use SageCompaniesTrait;
    use SageAccountancyPracticeTrait;
    use SageAccountingTrait;

    public const STATUS_NOT_FOUND = ['400', '401', '402', '403', '404', '415'];
    public const STATUS_ERROR_SERVER = ['500', '501', '503'];
    public const STATUS_NO_CONTENT = ['204'];


    private $em;
    private $cltHttpService;
    private $security;
    private $baseUrlApi;
    private $serializer;
    private $accountancyPractice;
    protected $requestStack;
    private $log;

    /**
     * ClientHttpService constructor.
     * @param EntityManagerInterface $em
     * @param ClientHttpService $cltHttpService
     * @param Security $security
     * @param SerializeService $serializeService
     * @param $baseUrlSageApi
     * @param RequestStack $requestStack
     * @param LoggerInterface $logger
     * @throws Exception
     */
    public function __construct(
        EntityManagerInterface $em,
        ClientHttpService $cltHttpService,
        Security $security,
        SerializeService $serializeService,
        $baseUrlSageApi,
        RequestStack $requestStack,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $request = $this->requestStack->getCurrentRequest();
        $this->accountancyPractice = ($request->attributes->get('accountPractice')) ? $request->attributes->get(
            'accountPractice'
        ) : '';
        $this->cltHttpService = $cltHttpService;
        $this->security = $security;
        if ($this->loginSageByAccountancyPractice() === false) {
            $this->loginSage();
        }
        $this->serializer = $serializeService;
        $this->baseUrlApi = $baseUrlSageApi;
        $this->log = $logger;

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
        if(($result["status"]==200) ||($result["status"]==201)){
            $response["content"]=$result["content"];
        }else{
            $response["content"]="error";
        }
        return $response;
    }
    /**
     * Get Entries function
     *
     * @param string $accountPractice
     * @param string $companyId
     * @param string $periodId
     * @param integer $page
     * @return void
     */
    public function getEntries(string $accountPractice,string $companyId,string $periodId)
    {       
        $sageModel=$this->ConnectedSageModel;
        $response=[];
        $appId=$sageModel->getAppId();
        $tokenAccess=$sageModel->getToken();
        $url=$this->baseUrlApi.'/applications/'.$appId.'/accountancypractices/'.$accountPractice.'/companies/'.$companyId.'/accounting/periods/'.$periodId.'/entries';
        $result = $this->cltHttpService->execute($url,"GET",[],$tokenAccess);
        $response["status"] =  $result["status"];
        if(($result["status"]==200) ||($result["status"]==201)){
            $response["content"]=$result["content"];
        }else{
            $response["content"]="error";
        }
        return $response;
    }
	/**
     * get Companies function
     *
     * @param string $accountPractice
     * @return void
     */
	public function getCompanies(string $accountPractice){
        $sageModel=$this->ConnectedSageModel;
        $response = [];
        $app_id=$sageModel->getAppId();
        $tokenAccess=$sageModel->getToken();
        $url=$this->baseUrlApi.'/applications/'.$app_id.'/accountancypractices/'.$accountPractice.'/companies';
        $result= $this->cltHttpService->execute($url,"GET",[],$tokenAccess);
        $response["status"] =  $result["status"];
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

    /**
     * Create Batch function
     *
     * @return void
     */
    public function createBatch()
    {
        $sageModel = $this->ConnectedSageModel;
        $app_id = $sageModel->getAppId();
        $tokenAccess = $sageModel->getToken();
        $accountPractice = '5a84d143-5fb1-4fce-bac0-b19ec942231c';
        $companyId = '22df8495-6357-44b2-8ea0-05272756d1da';
        $url = $this->baseUrlApi.'/applications/{applicationId}/accountancypractices/'.$accountPractice.'/companies/'.$companyId.'/queues/in/batches';

        return $this->cltHttpService->execute($url, "POST", [], $tokenAccess);
    }

    /**
     * @param string $url
     * @param $tokenAccess
     * @param $savingResponseMethodName
     * @param array $params
     * @param $entityClass
     * @param $repositoryMethodName
     * @param $criteria
     * @return mixed|string|null
     */
    public function saveAndGetDataByEntity(
        string $url,
        $tokenAccess,
        $savingResponseMethodName,
        array $params,
        $entityClass,
        $repositoryMethodName,
        $criteria
    ) {
        $result = $this->cltHttpService->execute($url, "GET", [], $tokenAccess);

        if (in_array($result["status"], $this::STATUS_ERROR_SERVER)) {

            return $result["content"];
        } else {

            if (in_array($result["status"], $this::STATUS_NOT_FOUND) || in_array(
                    $result["status"],
                    $this::STATUS_NO_CONTENT
                )) {

                return $this->serializer->serializeContent(
                    $this->em->getRepository($entityClass)->{$repositoryMethodName}($criteria)
                );
            } else {
                if ($this->allNotEmpty($result["content"], ...$params)) {
                    array_unshift($params, json_decode($result["content"], true));

                    $this->{$savingResponseMethodName}(...$params);
                }

                return $result["content"];
            }
        }
    }

    /**
     * @param mixed ...$params
     * @return bool
     */
    public function allNotEmpty(...$params): bool
    {
        foreach ($params as $param) {

            if (empty($param)) {
                return false;
            }
        }

        return true;
    }
    /**
     * get Invoicing Entities function
     *
     * @param string $applicationId
     * @param string $accountPractice
     * @param string $companyId
     * @return void
     */
    public function getInvoicingEnities(string $applicationId,string $accountPractice,string $companyId){
        $sageModel=$this->ConnectedSageModel;
        $app_id=(!empty($sageModel->getAppId())) ? $sageModel->getAppId() : $applicationId;
        $tokenAccess=$sageModel->getToken();
        $url=$this->baseUrlApi.'/applications/'.$app_id.'/accountancypractices/'.$accountPractice.'/companies/'.$companyId.'/invoicing/entities';
        $result = $this->cltHttpService->execute($url,"GET",[],$tokenAccess);
        $response["status"] =  $result["status"];
        if(($result["status"]==200) ||($result["status"]==201)){
            $response["content"]=$result["content"];
        }else{
            $response["content"]="error";
        }
        return $response;
    }
    /**
     * get Invoicing Companies function
     *
     * @param string $applicationId
     * @param string $accountPractice
     * @param string $companyId
     * @return void
     */
    public function getInvoicingCompanies(string $applicationId,string $accountPractice,string $companyId){
        $sageModel=$this->ConnectedSageModel;
        $app_id=(!empty($sageModel->getAppId())) ? $sageModel->getAppId() : $applicationId;
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi.'/applications/'.$app_id.'/accountancypractices/'.$accountPractice.'/companies/'.$companyId.'/invoicing/companies';
        $result = $this->cltHttpService->execute($url,"GET",[],$tokenAccess);
        $response["status"] =  $result["status"];
        if(($result["status"]==200) ||($result["status"]==201)){
            $response["content"]=$result["content"];
        }else{
            $response["content"]="error";
        }
        return $response;
    }
    /**
     * get Invoicing Activities function
     *
     * @param string $applicationId
     * @param string $accountPractice
     * @param string $companyId
     * @return void
     */
    public function getInvoicingActivities(string $applicationId,string $accountPractice,string $companyId){
        $sageModel=$this->ConnectedSageModel;
        $app_id=(!empty($sageModel->getAppId())) ? $sageModel->getAppId() : $applicationId;
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi.'/applications/'.$app_id.'/accountancypractices/'.$accountPractice.'/companies/'.$companyId.'/invoicing/activities';
        $result = $this->cltHttpService->execute($url,"GET",[],$tokenAccess);
        $response["status"] =  $result["status"];
        if(($result["status"]==200) ||($result["status"]==201)){
            $response["content"]=$result["content"];
        }else{
            $response["content"]="error";
        }
        return $response;
    }
    /**
     * get Invoicing Employees function
     *
     * @param string $applicationId
     * @param string $accountPractice
     * @param string $companyId
     * @return void
     */
    public function getInvoicingEmployees(string $applicationId,string $accountPractice,string $companyId){
        $sageModel = $this->ConnectedSageModel;
        $app_id = (!empty($sageModel->getAppId())) ? $sageModel->getAppId() : $applicationId;
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi.'/applications/'.$app_id.'/accountancypractices/'.$accountPractice.'/companies/'.$companyId.'/invoicing/employees';
        $result = $this->cltHttpService->execute($url,"GET",[],$tokenAccess);
        $response["status"] =  $result["status"];
        if(($result["status"]==200) ||($result["status"]==201)){
            $response["content"]=$result["content"];
        }else{
            $response["content"]="error";
        }
        return $response;
    }
    /**
     * get Invoicing Projects function
     *
     * @param string $applicationId
     * @param string $accountPractice
     * @param string $companyId
     * @return void
     */
    public function getInvoicingProjects(string $applicationId,string $accountPractice,string $companyId){
        $sageModel = $this->ConnectedSageModel;
        $app_id = (!empty($sageModel->getAppId())) ? $sageModel->getAppId() : $applicationId;
        $tokenAccess = $sageModel->getToken();
        $url = $this->baseUrlApi.'/applications/'.$app_id.'/accountancypractices/'.$accountPractice.'/companies/'.$companyId.'/invoicing/projects';
        $result = $this->cltHttpService->execute($url,"GET",[],$tokenAccess);
        $response["status"] =  $result["status"];
        if(($result["status"]==200) ||($result["status"]==201)){
            $response["content"]=$result["content"];
        }else{
            $response["content"]="error";
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
    public function createTimeLiles(string $applicationId,string $accountPractice,string $companyId,$params){
        $sageModel=$this->ConnectedSageModel;
        $appId=$sageModel->getAppId();
        $tokenAccess=$sageModel->getToken();
        $url = $this->baseUrlApi.'/applications/'.$appId.'/accountancypractices/'.$accountPractice.'/companies/'.$companyId.'/invoicing/timelines';
        $response=[];
        $result=$this->cltHttpService->execute($url,"POST",$params,$tokenAccess,2);
        if(($result["status"]==200) ||($result["status"]==201)){
            $response["content"]=$result["content"];
        }else{
            $response["content"]="error";
        }
        return $response;
    }


}