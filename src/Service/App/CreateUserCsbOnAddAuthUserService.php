<?php

namespace App\Service\App;

use App\Entity\Csb\UserCsb;
use Doctrine\ORM\EntityManagerInterface;

class CreateUserCsbOnAddAuthUserService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param int $idAuthUser
     * 
     * @return void
     */
    public function CreateUserCsbOnAuthUserCreated(int $idAuthUser)
    {
        $userCsb = new UserCsb();
        $userCsb->setId($idAuthUser);
        $this->em->persist($userCsb);
        $this->em->flush();
    }

}