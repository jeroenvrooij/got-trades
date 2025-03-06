<?php

namespace App\Repository;

use App\Entity\Card;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Card>
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    /**
    * @return Card[] Returns an array of Card objects
    */
    public function findBySet(string $setId): array
    {
        $qb = $this->createQueryBuilder('c');
        return $qb
            ->leftJoin('c.printings', 'p', Expr\Join::WITH, $qb->expr()->eq('p.card', 'c.uniqueId'))
            ->andWhere('p.setId = :setId')
            ->setParameter('setId', $setId)
            ->orderBy('c.name', 'ASC')
            ->groupBy('c.uniqueId')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;   
    }
}
