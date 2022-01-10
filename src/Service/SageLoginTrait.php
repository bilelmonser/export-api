<?php

namespace App\Service;

use App\Entity\SageModel;
use App\Entity\User;
use Exception;

trait SageLoginTrait
{


    private $ConnectedUser;
    private $ConnectedSageModel;

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
                $now->add(new \DateInterval('PT'.$response["content"]["expires_in"].'S'));
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
                $now->add(new \DateInterval('PT'.$response["content"]["expires_in"].'S'));
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