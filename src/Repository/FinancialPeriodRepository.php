<?php

namespace App\Repository;

use App\Entity\FinancialPeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FinancialPeriod|null find($id, $lockMode = null, $lockVersion = null)
 * @method FinancialPeriod|null findOneBy(array $criteria, array $orderBy = null)
 * @method FinancialPeriod[]    findAll()
 * @method FinancialPeriod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FinancialPeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FinancialPeriod::class);
    }

    /**
    * @return Company[] Returns an array of Company objects
    */
    
    public function findByCompanyAll($value)
    {
        return $this->createQueryBuilder('f')
            ->select('f.code','f.financialPeriodName','c.startDate','c.endDate','f.closed','f.ExtrasFirstFinancialDate as extras.firstFinancialDate','c.ExtrasFiscalEndOfTheFirstFiscalPeriod as extras.fiscalEndOfTheFirstFiscalPeriod','c.ExtrasAccountLabelLength as extras.accountLabelLength','f.ExtrasTradingAccountLength as extras.tradingAccountLength','f.ExtrasAccountingLineLabelLength as extras.accountingLineLabelLength','c.ExtrasAccountLength as extras.accountLength','c.ExtrasAuthorizationAlphaAccounts as extras.authorizationAlphaAccounts','f.ExtrasAmountsLength as extras.amountsLength','f.ExtrasWithQuantities as extras.withQuantities','c.ExtrasWithDueDates as extras.withDueDates','c.ExtrasWithMultipleDueDates as extras.withMultipleDueDates','f.uuid as $uuid')
            ->andWhere('c.company = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
        ;
    }
}
