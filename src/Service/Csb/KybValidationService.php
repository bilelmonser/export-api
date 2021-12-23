<?php

namespace App\Service\Csb;

use App\Entity\Csb\Constantes\AppConstantes;
use App\Entity\Csb\Document\Document;
use App\Entity\Csb\UserCsb;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class KybValidationService
{
    /**
     * @param array $arrayBody
     * @return array|null
     */
    public function getStatusValidation(array $arrayBody): ?array
    {

        if (null === $arrayBody['statusUser'] || !isset($arrayBody['statusUser']) || !in_array((int)$arrayBody['statusUser'], AppConstantes::USER_STATUS, true))
            return ['message' => 'You must specify a valid status validation for the user.', 'httpCode' => Response::HTTP_BAD_REQUEST];

        if (null === $arrayBody['statusDocument'] || !isset($arrayBody['statusDocument']) || !in_array((int)$arrayBody['statusDocument'], AppConstantes::USER_STATUS, true))
            return ['message' => 'You must specify the status validation for the document.', 'httpCode' => Response::HTTP_BAD_REQUEST];

        return null;
    }
}
