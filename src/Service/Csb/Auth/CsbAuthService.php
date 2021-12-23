<?php

namespace App\Service\Csb\Auth;

use App\Entity\AuthUser;
use App\Entity\Csb\Constantes\AppConstantes;
use App\Entity\Csb\UserCsb;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CsbAuthService
{

    private UserPasswordHasherInterface $passwordHasher;
    private ValidatorInterface $validator;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface          $validator
    )
    {
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
    }

    /**
     * @param string $email
     * @param string $planePassword
     * @param int $userTypeId
     *
     * @return array
     */
    public function setAuthUser(string $email, string $planePassword, int $userTypeId): array
    {
        $authUser = new AuthUser();
        $authUser->setEmail($email)
            ->setPassword($this->passwordHasher->hashPassword($authUser, $planePassword))
            ->setRoles(AppConstantes::USER_ROLE[$userTypeId]);

        $errors = $this->validator->validate($authUser);

        return [$errors, $authUser];
    }

    /**
     * @param EntityManagerInterface $em
     * @param AuthUser $authUser
     *
     * @return bool
     */
    public function persistAuthUser(EntityManagerInterface $em, AuthUser $authUser): bool
    {
        try {
            $em->persist($authUser);
            $em->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string $email
     * @param EntityManagerInterface $em
     *
     * @return bool
     */
    public function checkEmailUniqueness(string $email, EntityManagerInterface $em): bool
    {
        $isUnique = true;
        if (null !== $em->getRepository(AuthUser::class)->findOneBy(array('email' => $email))) $isUnique = false;
        return $isUnique;
    }

}