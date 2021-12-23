<?php

namespace App\Repository;

use App\Entity\SageModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SageModel|null find($id, $lockMode = null, $lockVersion = null)
 * @method SageModel|null findOneBy(array $criteria, array $orderBy = null)
 * @method SageModel[]    findAll()
 * @method SageModel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SageModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SageModel::class);
    }

    // /**
    //  * @return SageModel[] Returns an array of SageModel objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SageModel
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
