<?php

namespace App\Repository;

use App\Entity\CardPrinting;
use App\Entity\Set;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CardPrinting>
 */
class CardPrintingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CardPrinting::class);
    }

    /**
    * @return CardPrinting[] Returns an array of CardPrinting objects
    */
    public function findBySet(Set $set): array
    {
            $qb = $this->createQueryBuilder('cp');
        return $qb
                ->select('cp, c')
                ->innerJoin('cp.card', 'c', Expr\Join::WITH, $qb->expr()->eq('cp.card', 'c.uniqueId'))
                ->andWhere('cp.setId = :setId')
                ->setParameter('setId', $set->getId())
                ->orderBy('cp.cardId', 'ASC')
                // ->setMaxResults(10)
                ->getQuery()
                ->getResult()
            ;   
    }
}
