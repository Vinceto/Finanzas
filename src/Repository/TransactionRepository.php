<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @extends ServiceEntityRepository<Transaction>
 *
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function add(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Transaction $entity, bool $flush = false): void
    {
        $entity->setDeletedAt(new \DateTime());

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function calculateTotalByType(string $type): float
    {
        $qb = $this->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->where('t.type = :type')
            ->andWhere('t.deletedAt IS NULL')
            ->setParameter('type', $type)
            ->orderBy('t.date', 'ASC')
            ->getQuery();

        return (float) $qb->getSingleScalarResult();
    }

    public function findByCurrency($currency)
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.deletedAt IS NULL')
            ->orderBy('t.date', 'ASC');

        if ($currency === 'CLP' || $currency === 'USD') {
            $qb->andWhere('t.currency = :currency')
               ->setParameter('currency', $currency);
        }

        return $qb->getQuery()->getResult();
    }
    
    public function calculateTotalBalanceByMonthYearAndCurrency($month, $year, $currency)
    {
        // Crear fecha de inicio (primer día del mes)
        $fromDate = new \DateTime("$year-$month-01");
        // Crear fecha de fin (último día del mes)
        $toDate = new \DateTime("$year-$month-" . $fromDate->format('t'));
        $result = 0;

        if ($currency === 'CLP' || $currency === 'USD') {
            $incomeQb = $this->createQueryBuilder('t')
                ->select('SUM(t.amount)')
                ->andWhere('t.date BETWEEN :fromDate AND :toDate')
                ->andWhere('t.currency = :currency')
                ->andWhere('t.type = :incomeType')
                ->setParameter('fromDate', $fromDate)
                ->setParameter('toDate', $toDate)
                ->setParameter('currency', $currency)
                ->setParameter('incomeType', 'income');

            $expenseQb = $this->createQueryBuilder('t')
                ->select('SUM(t.amount)')
                ->andWhere('t.date BETWEEN :fromDate AND :toDate')
                ->andWhere('t.currency = :currency')
                ->andWhere('t.type = :expenseType')
                ->setParameter('fromDate', $fromDate)
                ->setParameter('toDate', $toDate)
                ->setParameter('currency', $currency)
                ->setParameter('expenseType', 'expense');

            $income = $incomeQb->getQuery()->getSingleScalarResult();
            $expense = $expenseQb->getQuery()->getSingleScalarResult();

            $result = $income - $expense;
        }

        return $result;
    }

    public function findUniqueYears(): array
    {
        $entityManager = $this->getEntityManager();
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('year', 'year');

        $query = $entityManager->createNativeQuery('
        SELECT DISTINCT EXTRACT(YEAR FROM date) AS year FROM transaction ORDER BY year ASC
        ', $rsm);

        return $query->getResult();
    }

    public function findByMonthAndYearAndCurrency($month, $year, $currency = null)
    {
        // Crear fecha de inicio (primer día del mes)
        $fromDate = new \DateTime("$year-$month-01");
        // Crear fecha de fin (último día del mes)
        $toDate = new \DateTime("$year-$month-" . $fromDate->format('t'));

        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.deletedAt IS NULL')
            ->andWhere('t.date BETWEEN :fromDate AND :toDate')
            ->setParameter('fromDate', $fromDate)
            ->setParameter('toDate', $toDate)
            ->orderBy('t.date', 'ASC');

        if ($currency === 'CLP' || $currency === 'USD') {
            $qb->andWhere('t.currency = :currency')
            ->setParameter('currency', $currency);
        }

        return $qb->getQuery()->getResult();
    }



}
