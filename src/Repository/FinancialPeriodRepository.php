<?php

namespace App\Repository;

use App\Entity\Company;
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


    public const FINANCIAL_PERIOD_ORIGINAL_KEYS = [
        'code',//  code
        'financialPeriodName',//  financialPeriodName
        'startDate',//  startDate
        'endDate',//  endDate
        'closed',//  closed
        'extras.firstFinancialDate',//  extrasFirstFinancialDate
        'extras.fiscalEndOfTheFirstFiscalPeriod',//  extrasFiscalEndOfTheFirstFiscalPeriod
        'extras.accountLabelLength',//  extrasAccountLabelLength
        'extras.tradingAccountLength',//  extrasTradingAccountLength
        'extras.accountingLineLabelLength',//  extrasAccountingLineLabelLength
        'extras.accountLength',//  extrasAccountLength
        'extras.authorizationAlphaAccounts',//  extrasAuthorizationAlphaAccounts
        'extras.amountsLength',//  extrasAmountsLength
        'extras.withQuantities',//  extrasWithQuantities
        'extras.withDueDates',//  extrasWithDueDates
        'extras.withMultipleDueDates',//  extrasWithMultipleDueDates
        '$uuid'//  uuid
    ];


    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FinancialPeriod::class);
    }

    /**
     * @param $company
     * @return Company[] Returns an array of Company objects
     */
    public function findByCompanyAll($company): array
    {
        $allFinPeriodsFromDB = $this->createQueryBuilder('f')
            ->select('f.code','f.financialPeriodName','f.startDate','f.endDate','f.closed','f.extrasFirstFinancialDate','f.extrasFiscalEndOfTheFirstFiscalPeriod','f.extrasAccountLabelLength','f.extrasTradingAccountLength','f.extrasAccountingLineLabelLength','f.extrasAccountLength','f.extrasAuthorizationAlphaAccounts','f.extrasAmountsLength','f.extrasWithQuantities','f.extrasWithDueDates','f.extrasWithMultipleDueDates','f.uuid')
            ->andWhere('f.company = :val')
            ->setParameter('val', $company)
            ->getQuery()->getResult();

        $allFinPeriods = [];

        foreach ($allFinPeriodsFromDB as $resfrnbase){
            $allFinPeriods[] = array_combine(self::FINANCIAL_PERIOD_ORIGINAL_KEYS, array_values($resfrnbase));
        }

        return $allFinPeriods;
    }
}
