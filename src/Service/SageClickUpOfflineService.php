<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\SageModel;
use App\Service\ClientHttpService;
use Symfony\Component\Security\Core\Security;

class SageClickUpOfflineService
{
    private $em;
    private $cltHttpService;
    private $security;

    public function __construct(EntityManagerInterface $em,ClientHttpService $cltHttpService,Security $security)
    {
        $this->em=$em;
        $this->cltHttpService=$cltHttpService;
        $this->security = $security;

    }

    /**
     * check Accountancy Practices function
     *
     * @param array $params
     * @return void
     */
    public function checkAccountingPractices($params){
        $sageModel=$this->em->getRepository(SageModel::class)->findOneBy(['AccountancyPractice' => $params["accountancyPractices"]]);
        if(!empty($sageModel)){
            $sageModel->setAccountancyPractice($params["accountancyPractices"]);
            $sageModel->setAppId($params["appId"]);
            $sageModel->setClientId($params["clientId"]);
            $sageModel->setClientSecret($params["clientSecret"]);
            $this->em->persist($sageModel);
            $this->em->flush();
        }else{
            $sageModel=new SageModel();
            $sageModel->setAccountancyPractice($params["accountancyPractices"]);
            $sageModel->setAppId($params["appId"]);
            $sageModel->setClientId($params["clientId"]);
            $sageModel->setClientSecret($params["clientSecret"]);
            $this->em->persist($sageModel);
            $this->em->flush();
        }
        $result=[];
        $result["status"]=200;
        $result["content"]="parameters updated";
        return $result;
    }

}