<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    /**
    * @return Company[] Returns an array of Company objects
    */
    
    public function findByAccountancyPracticeAll($value)
    {
        return $this->createQueryBuilder('c')
            ->select('c.SageId as id','c.businessId','c.name','c.isAccountancyPractice')
            ->andWhere('c.accountancyPractice = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param $accountancyPractice
     * @return int|array|string
     */
    public function findCompanySageIdList($accountancyPractice)
    {
        return $this->createQueryBuilder('c')
            ->select('c.SageId')
            ->andWhere('c.accountancyPractice = :accountancyPractice')
            ->setParameter('accountancyPractice', $accountancyPractice)
            ->getQuery()
            ->getArrayResult()
        ;
    }

}
