<?php

namespace App\Service\Sage;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\SageModel;
use App\Service\Sage\ClientHttpService;
use Symfony\Component\Security\Core\Security;

class SageClickUpOfflineService
{
    private $em;
    private $cltHttpService;
    private $security;

    /**
     * @param EntityManagerInterface $em
     * @param \App\Service\Sage\ClientHttpService $cltHttpService
     * @param Security $security
     */
    public function __construct(EntityManagerInterface $em, ClientHttpService $cltHttpService, Security $security)
    {
        $this->em = $em;
        $this->cltHttpService = $cltHttpService;
        $this->security = $security;
    }


    /**
     * check Accountancy Practices function
     *
     * @param array $params
     * @return array
     */
    public function checkAccountingPractices(array $params): array
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $sageModel = $this->em->getRepository(SageModel::class)->findOneBy(['AccountancyPractice' => $params["accountancyPractices"]]);
        if (null === $sageModel) {
            $sageModel = new SageModel();
        }
        $sageModel->setIdUser($user);
        $sageModel->setAccountancyPractice($params["accountancyPractices"]);
        $sageModel->setAppId($params["appId"]);
        $sageModel->setClientId($params["clientId"]);
        $sageModel->setClientSecret($params["clientSecret"]);
//        TODO read expirationToken from config/packages/lexik_jwt_authentication.yaml:5
        $sageModel->setExpiredtoken(\DateTime::createFromFormat('Y-m-d', '2023-12-31'));
        $this->em->persist($sageModel);
        $this->em->flush();
        $result = [];
        $result["status"] = 200;
        $result["content"] = "parameters updated";
        return $result;
    }
}
