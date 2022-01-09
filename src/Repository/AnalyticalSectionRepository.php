<?php

namespace App\Repository;

use App\Entity\AnalyticalSection;
use App\Entity\FinancialAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AnalyticalSection|null find($id, $lockMode = null, $lockVersion = null)
 * @method AnalyticalSection|null findOneBy(array $criteria, array $orderBy = null)
 * @method AnalyticalSection[]    findAll()
 * @method AnalyticalSection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnalyticalSectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnalyticalSection::class);
    }
}