<?php

namespace App\Repository;

use App\Entity\AccountancyPractice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccountancyPractice|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountancyPractice|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountancyPractice[]    findAll()
 * @method AccountancyPractice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountancyPracticeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountancyPractice::class);
    }

     /**
      * @return AccountancyPractice[] Returns an array of AccountancyPractice objects
     */
    
    public function findBySageModelAll($sageModel)
    {
        return $this->createQueryBuilder('a')
            ->select("a.SageId as id","a.businessId","a.name","a.originSageApplication","a.contactEmail")
            ->andWhere('a.sageModel = :val')
            ->setParameter('val', $sageModel->getId())
            ->getQuery()
            ->getResult()
        ;
    }
}
