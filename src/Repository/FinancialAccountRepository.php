<?php

namespace App\Repository;

use App\Entity\FinancialAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FinancialAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method FinancialAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method FinancialAccount[]    findAll()
 * @method FinancialAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FinancialAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FinancialAccount::class);
    }
}