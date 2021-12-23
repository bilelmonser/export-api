<?php

namespace App\Repository;

use App\Entity\IbizaModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IbizaModel|null find($id, $lockMode = null, $lockVersion = null)
 * @method IbizaModel|null findOneBy(array $criteria, array $orderBy = null)
 * @method IbizaModel[]    findAll()
 * @method IbizaModel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IbizaModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IbizaModel::class);
    }

    // /**
    //  * @return IbizaModel[] Returns an array of IbizaModel objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?IbizaModel
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
