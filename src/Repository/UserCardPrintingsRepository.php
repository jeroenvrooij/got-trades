<?php

namespace App\Repository;

use App\Entity\Card;
use App\Entity\CardClass;
use App\Entity\CardPrinting;
use App\Entity\Set;
use App\Entity\User;
use App\Entity\UserCardPrintings;
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
    public function getCollectionDataForUser(User $user, ?Set $set = null, ?string $className = null): array
    {
        $qb = $this->createQueryBuilder('ucp')
            ->select('NEW App\Model\UserCollectionModel(cp.uniqueId, cp.cardId, ucp.collectionAmount)')
            ->innerJoin(CardPrinting::class, 'cp', Join::WITH, 'ucp.cardPrinting = cp.uniqueId')
            ->andWhere('ucp.user = :user')
            ->setParameter('user', $user)
            ;
            
        if (null !== $set){
            $qb
                ->andWhere('cp.set = :set')
                ->setParameter('set', $set)
            ;
        }
            
        if (null !== $className){
            $qb
                ->innerJoin(Card::class, 'c', Join::WITH, 'cp.card = c.uniqueId')
                ->innerJoin(CardClass::class, 'cc', Join::WITH, 'c.uniqueId = cc.card')
            
                ->andWhere('cc.className = :className')
                ->setParameter('className', ucfirst($className))
            ;
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }
}
