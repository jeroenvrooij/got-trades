<?php

namespace App\Repository;

use App\Entity\CardPrinting;
use App\Entity\User;
use App\Entity\UserCardPrintings;
use App\Model\CardPrintingsResultSet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserCardPrintings>
 */
class UserCardPrintingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCardPrintings::class);
    }

    /**
    * @return UserCardPrintings[] Returns an array of UserCardPrintings objects
    */
    public function getCollectionDataForPrintingsByUser(
        User $user,
        CardPrintingsResultSet $cardPrintingsResultSet
    ): array
    {
        $qb = $this->createQueryBuilder('ucp')
            ->select('NEW App\Model\UserCollectionModel(cp.uniqueId, cp.cardId, ucp.collectionAmount)')
            ->innerJoin(CardPrinting::class, 'cp', Join::WITH, 'ucp.cardPrinting = cp.uniqueId')
            ->andWhere('ucp.user = :user')
            ->setParameter('user', $user)
            ;


        $cardIds = $cardPrintingsResultSet->getUniqueCardIds();
        if (empty($cardIds)) {
            return [];
        }

        $qb
            ->andWhere('cp.uniqueId IN (:cardIds)')
            ->setParameter('cardIds', $cardIds)
        ;

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }
}
