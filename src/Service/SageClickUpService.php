<?php

namespace App\Service;

use App\Service\App\SerializeService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use App\Service\Sage\ClientHttpService;
use App\Entity\SageModel;

class SageClickUpService
{

    public const STATUS_NOT_FOUND = ['400', '401', '402', '403', '404', '415'];
    public const STATUS_ERROR_SERVER = ['500', '501', '503'];
    public const STATUS_NO_CONTENT = ['204'];


    protected $em;
    protected $cltHttpService;
    private $security;
    protected $baseUrlApi;
    protected $serializer;
    private $accountancyPractice;
    protected $requestStack;
    private $log;
    protected $ConnectedUser;
    protected $ConnectedSageModel;


    /**
     * ClientHttpService constructor.
     * @param EntityManagerInterface $em
     * @param ClientHttpService $cltHttpService
     * @param Security $security
     * @param SerializeService $serializeService     
     * @param RequestStack $requestStack
     * @param LoggerInterface $logger
     * @param $baseUrlSageApi
     * @throws Exception
     */
    public function __construct(
        $baseUrlSageApi,
        EntityManagerInterface $em,
        ClientHttpService $cltHttpService,
        Security $security,
        SerializeService $serializeService,
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
        $this->log = $logger;
        $this->baseUrlApi = $baseUrlSageApi;
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
    public function createEntry(string $accountPractice, string $companyId, string $periodId, $attachement, $entry)
    {
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
        if (($result["status"] == 200) || ($result["status"] == 201)) {
            $response["content"] = $result["content"];
        } else {
            $response["content"] = "error";
        }
        return $response;
    }

    /**
     * Login Sage ClickUp function
     *
     * @return void
     * @throws Exception
     */
    private function loginSage()
    {
        $user = $this->security->getUser();
        $sageModel = $user->getSageconfigs()->first();
        $today = date("Y-m-d H:i:s");
        $dateExpired = $sageModel->getExpiredtoken()->format('Y-m-d H:i:s');
        if (empty($sageModel->getToken()) || (!empty($sageModel->getToken()) && ($today > $dateExpired))) {
            $url_auth = $user->getSageconfigs()->first()->getUrlAuth();
            $grant_type = $user->getSageconfigs()->first()->getGrantType();
            $client_id = $user->getSageconfigs()->first()->getClientId();
            $client_secret = $user->getSageconfigs()->first()->getClientSecret();
            $audience = $user->getSageconfigs()->first()->getAudience();
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
                $now->add(new \DateInterval('PT' . $response["content"]["expires_in"] . 'S'));
                $sageModel->setToken($response["content"]["access_token"]);
                $sageModel->setExpiredToken($now);
                $this->em->persist($sageModel);
                $this->em->flush();
            } else {
                return false;
            }
            $this->ConnectedUser = $this->em->getRepository(User::class)->findOneBy(['email' => 'admin2@admin.com']);
            $this->ConnectedSageModel = $this->ConnectedUser->getSageconfigs()->first();
        } else {
            $this->ConnectedUser = $user;
            $this->ConnectedSageModel = $user->getSageconfigs()->first();
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
        $sageModel = $this->em->getRepository(SageModel::class)->findOneBy(
            ['AccountancyPractice' => $this->accountancyPractice]
        );

        $today = date("Y-m-d H:i:s");
        if (empty($sageModel)) {
            return false;
        }
        $dateExpired = $sageModel->getExpiredtoken()->format('Y-m-d H:i:s');

        if (empty($sageModel->getToken()) || (!empty($sageModel->getToken()) && ($today > $dateExpired))) {
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
                $now->add(new \DateInterval('PT' . $response["content"]["expires_in"] . 'S'));
                $sageModel->setToken($response["content"]["access_token"]);
                $sageModel->setExpiredToken($now);
                $this->em->persist($sageModel);
                $this->em->flush();
            } else {
                return false;
            }
            $this->ConnectedSageModel = $this->em->getRepository(SageModel::class)->findOneBy(
                ['AccountancyPractice' => $this->accountancyPractice]
            );
        } else {
            $this->ConnectedSageModel = $sageModel;
        }
    }
}
