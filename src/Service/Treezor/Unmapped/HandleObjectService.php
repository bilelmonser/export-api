<?php

namespace App\Service\Treezor\Unmapped;

use App\Entity\Csb\UserCsb;
use App\Entity\Treezor\Unmapped\CorporationUser;

class HandleObjectService
{
    public function setUnmappedCorporationUser(UserCsb $user)
    {
        $handledObject = new CorporationUser();
        $handledObject->setUserTypeId($user->getUserTypeId())
            ->setLegalName($user->getLegalName())
            ->setLegalRegistrationNumber($user->getLegalRegistrationNumber())
            ->setLegalRegistrationDate($user->getLegalRegistrationDate())
            ->setLegalForm($user->getLegalForm())
            ->setLegalShareCapital($user->getLegalShareCapital())
            ->setAddress1($user->getAddress1())
            ->setPostcode($user->getPostcode())
            ->setCity($user->getCity())
            ->setCountry($user->getCountry())
            ->setEmail($user->getEmail())
            ->setPhone($user->getPhone())
            ->setLegalNumberOfEmployeeRange($user->getLegalNumberOfEmployeeRange())
            ->setLegalSector($user->getLegalSector())
            ->setLegalTvaNumber($user->getLegalTvaNumber())
            ->setLegalAnnualTurnOver($user->getLegalAnnualTurnOver())
            ->setLegalNetIncomeRange($user->getLegalNetIncomeRange())
            ->setSpecifiedUSPerson($user->getSpecifiedUSPerson());

        return $handledObject;
    }
}