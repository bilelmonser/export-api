<?php

namespace App\Service;

use App\Entity\User;
use App\Service\App\SerializeService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use App\Service\Sage\ClientHttpService;
use App\Entity\SageModel;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SageClickUpService
{

    public const STATUS_NOT_FOUND = ['400', '401', '402', '403', '404', '415'];
    public const STATUS_ERROR_SERVER = ['500', '501', '503'];
    public const STATUS_NO_CONTENT = ['204'];


    protected EntityManagerInterface $em;
    protected ClientHttpService $cltHttpService;
    private Security $security;
    protected $baseUrlApi;
    protected SerializeService $serializer;
    private $accountancyPractice;
    protected RequestStack $requestStack;
    private LoggerInterface $log;
    protected $ConnectedUser;
    protected $ConnectedSageModel;
    protected HttpClientInterface $client;


    /**
     * ClientHttpService constructor.
     * @param $baseUrlSageApi
     * @param EntityManagerInterface $em
     * @param ClientHttpService $cltHttpService
     * @param Security $security
     * @param SerializeService $serializeService
     * @param RequestStack $requestStack
     * @param LoggerInterface $logger
     * @param HttpClientInterface $client
     * @throws Exception
     */
    public function __construct(
        $baseUrlSageApi,
        EntityManagerInterface $em,
        ClientHttpService $cltHttpService,
        Security $security,
        SerializeService $serializeService,
        RequestStack $requestStack,
        LoggerInterface $logger,
        HttpClientInterface $client
    ) {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $request = $this->requestStack->getCurrentRequest();
        $this->accountancyPractice = ($request->attributes->get('accountPractice')) ? $request->attributes->get(
            'accountPractice'
        ) : '';
        $this->cltHttpService = $cltHttpService;
        $this->security = $security;
		$this->loginSageByAccountancyPractice();
        $this->serializer = $serializeService;
        $this->log = $logger;
        $this->baseUrlApi = $baseUrlSageApi;
        $this->client = $client;
    }

    public function getSageModel($user = null){
        if($user === null){
            $user = $this->security->getUser();
        }

        $sageModels = $user->getSageconfigs();
        foreach ($sageModels as $sageModel){
            $this->ConnectedUser = $user;
            $this->ConnectedSageModel = $sageModel;
            break;
        }

    }

    /**
     * Login Sage ClickUp function
     *
     * @return void
     * @throws Exception
     */
    private function loginSageByAccountancyPractice()
    {
    	/** @var SageModel $sageModel */
        $sageModel = $this->em->getRepository(SageModel::class)->findOneBy(
            ['AccountancyPractice' => $this->accountancyPractice]
        );

        $today = date("Y-m-d H:i:s");

        if (empty($sageModel)) {
            return null;
        }

        $dateExpired = $sageModel->getExpiredtoken() ? $sageModel->getExpiredtoken()->format('Y-m-d H:i:s') : null;

        if (empty($sageModel->getToken()) || empty($dateExpired) || (!empty($sageModel->getToken()) && ($today > $dateExpired))) {
            $url_auth = $sageModel->getUrlAuth();
            $grant_type = $sageModel->getGrantType();
            $client_id = $sageModel->getClientId();
            $client_secret = $sageModel->getClientSecret();
            $audience = $sageModel->getAudience();
            $response = $this->cltHttpService->execute(
                $url_auth,
                "POST",
                [
                    "grant_type" => $grant_type,
                    "client_id" => $client_id,
                    "client_secret" => $client_secret,
                    "audience" => $audience,
                ],
                "",
                1
            );

            $response["content"] = json_decode($response["content"], true);
            if (isset($response["content"]["access_token"])) {
                $now = new \DateTime();
                $now->add(new \DateInterval('PT'.$response["content"]["expires_in"].'S'));
                $sageModel->setToken($response["content"]["access_token"]);
                $sageModel->setExpiredToken($now);
                $this->em->persist($sageModel);
                $this->em->flush();
            } else {
                return null;
            }
            $this->ConnectedSageModel = $sageModel;
        }

		$this->ConnectedSageModel = $sageModel;
    }
}
