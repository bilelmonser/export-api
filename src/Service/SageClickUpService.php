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
    private $ConnectedUser;
    private $ConnectedSageModel;
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


}