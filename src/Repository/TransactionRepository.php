<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

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
            ->getQuery();

        return (float) $qb->getSingleScalarResult();
    }

    public function findByCurrency($currency)
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.deletedAt IS NULL');

        if ($currency === 'CLP' || $currency === 'USD') {
            $qb->andWhere('t.currency = :currency')
               ->setParameter('currency', $currency);
        }

        return $qb->getQuery()->getResult();
    }
    
    public function calculateTotalBalance($currency)
    {
        $transactions = $this->findByCurrency($currency);
        $totalBalance = 0;
        if(!is_null($currency)){
            foreach ($transactions as $transaction) {
                if ($transaction->getType() === 'income') {
                    $totalBalance += $transaction->getAmount();
                } else {
                    $totalBalance -= $transaction->getAmount();
                }
            }
        }
        return $totalBalance;
    }
    
}
