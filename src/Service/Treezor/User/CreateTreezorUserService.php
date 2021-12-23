<?php

namespace App\Service\Treezor\User;

use App\Entity\Csb\Constantes\AppConstantes;
use App\Entity\Csb\UserCsb;
use App\Entity\Treezor\Constantes\TreezorConstantes;
use App\Entity\Treezor\Users\User;
use App\Service\App\SerializeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CreateTreezorUserService
{

    /**
     * @param UserCsb $data
     * 
     * @return array
     */
    public function isValidData(UserCsb $data): array
    {
        $isValid = false;

        $message = [
            0 => '',
            1 => 'User type unsupported by Treezor Api.',
            2 => "You can not create this resource, probably is not validated FACNOT or it's canceled.",
            3 => 'User all ready sent to Treezor Api, you can not recreate this entry.'
        ];

        if (!(in_array($data->getUserTypeId(), TreezorConstantes::USER_TYPE_ID))) {
            return [$isValid, $message[1]];
        }

        if ($data->getStatusValidation() !== AppConstantes::USER_STATUS['VALIDATED']) {
            return [$isValid, $message[2]];
        }

        if ($data->getTreezorStatusValidation() !== AppConstantes::USER_STATUS['NONE']) {
            return [$isValid, $message[3]];
        }

        return [!$isValid, $message[0]];
    }

    /**
     * @param ResponseInterface $response
     * @param SerializeService $normalizer
     * 
     * @return array
     */
    public function handleReceivingData(ResponseInterface $response, SerializeService $normalizer): array
    {
        $arrayObjectUser = [];
        $jsonString = $response->getContent();
        $decodedData = json_decode($jsonString);

        foreach ($decodedData as $data) {
            foreach ($data as $dat) {
                $arrayObjectUser[] = $normalizer->DenormalizeContent($dat, User::class);
            }
        }
        return $arrayObjectUser;
    }
}
