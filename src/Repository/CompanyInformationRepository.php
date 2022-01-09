<?php

namespace App\Repository;

use App\Entity\CompanyInformation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CompanyInformation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyInformation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyInformation[]    findAll()
 * @method CompanyInformation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyInformationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyInformation::class);
    }
}