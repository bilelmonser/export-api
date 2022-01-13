<?php

namespace App\Repository;

use App\Entity\Invoicing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Invoicing|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invoicing|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invoicing[]    findAll()
 * @method Invoicing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoicingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoicing::class);
    }

    // /**
    //  * @return Invoicing[] Returns an array of Invoicing objects
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
    public function findOneBySomeField($value): ?Invoicing
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
