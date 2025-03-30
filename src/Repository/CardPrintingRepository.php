<?php

namespace App\Repository;

use App\Entity\CardFaceAssociation;
use App\Entity\CardPrinting;
use App\Entity\Set;
use App\Entity\UserCardPrintings;
use App\Exception\NoCardsFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<CardPrinting>
 */
class CardPrintingRepository extends ServiceEntityRepository
{
    private CardFaceAssociationRepository $cardFaceAssociationRepository;

    private UserCardPrintingsRepository $userCardPrintingsRepository;

    private Security $security;

    public function __construct(
        ManagerRegistry $registry, 
        CardFaceAssociationRepository $cardFaceAssociationRepository,
        UserCardPrintingsRepository $userCardPrintingsRepository,
        Security $security,
    ) {
        parent::__construct($registry, CardPrinting::class);
        $this->cardFaceAssociationRepository = $cardFaceAssociationRepository;
        $this->userCardPrintingsRepository = $userCardPrintingsRepository;
        $this->security = $security;
    }

    /**
    * @return CardPrinting[] Returns an array of CardPrinting objects
    */
    public function findBySetAndFoiling(Set $set, ?string $foiling = '', ?bool $hideOwnedCards = false, ?string $cardName = ''): array
    {
        $qb = $this->createQueryBuilder('cp');

        $qb
            ->select('cp, c')
            ->innerJoin('cp.card', 'c')
            ->andWhere('cp.setId = :setId')
            ->setParameter('setId', $set->getId())
            ->andWhere(
                // clause needed for filtering double sided prints
                $qb->expr()->orX(
                    // Keep all front printings
                    $qb->expr()->in(
                        'cp.uniqueId',
                        $this->cardFaceAssociationRepository->createQueryBuilder('cfa')
                            ->select('identity(cfa.frontCardPrinting)')
                            ->getDQL()
                    ),
                    /*
                    * Remove back printings, if front and back are the same card. Eg both UPR006
                    * 
                    * Match on card id and not card unique id so that a double sided cards with two different
                    * cards on it is not filtered. Eg Storm of Sandikai (UPR003) on front and Fai (UPR045) on back.
                    * 
                    */
                    $qb->expr()->not(
                        $qb->expr()->exists(
                            $this->cardFaceAssociationRepository->createQueryBuilder('cfa2')
                                ->select('1')
                                ->innerJoin('cfa2.frontCardPrinting', 'frontPrinting')
                                ->innerJoin('cfa2.backCardPrinting', 'backPrinting')
                                ->where('cfa2.backCardPrinting = cp.uniqueId')
                                ->andWhere('frontPrinting.cardId = backPrinting.cardId')
                                ->getDQL()
                        )
                    )
                )
            )
            ->addOrderBy('cp.cardId', 'ASC')
            ->addOrderBy('cp.foiling', 'DESC')
            ->addOrderBy('cp.artVariations', 'DESC')
            ;
                // ->setMaxResults(10)
        if($foiling) {
            $qb
                ->andWhere('cp.foiling = :foiling')
                ->setParameter('foiling', $foiling)
                ;
        }
        if($cardName) {
            $qb
                ->andWhere('LOWER(c.name) LIKE :cardName')
                ->setParameter('cardName', '%' . strtolower($cardName) . '%')
                ;
        }

        if ($hideOwnedCards) {
            $qb->andWhere(
                // When hiding completed playsets the card printing should not exists in this subsets of completed playsets
                $qb->expr()->not(
                    $qb->expr()->exists(
                        $this->userCardPrintingsRepository->createQueryBuilder('ucp')
                            ->select('1')
                            ->where('ucp.cardPrintingUniqueId = cp')
                            ->andWhere('ucp.user = :userId')
                            ->andWhere('
                                (
                                    (
                                        -- for some type of cards a playset is completed is you have one copy
                                        array_to_string(c.types, \',\') LIKE \'%Hero%\' OR
                                        array_to_string(c.types, \',\') LIKE \'%Equipment%\' OR
                                        array_to_string(c.types, \',\') LIKE \'%Token%\' OR
                                        array_to_string(c.types, \',\') LIKE \'%Weapon%\' AND
                                        array_to_string(c.types, \',\') LIKE \'%2H%\'
                                    )
                                    AND ucp.collectionAmount >=1
                                )
                                OR 
                                (
                                    (   
                                        -- for 1 hander weapons a playset consists of two copies
                                        array_to_string(c.types, \',\') LIKE \'%1H%\' AND
                                        array_to_string(c.types, \',\') LIKE \'%Weapon%\'
                                    )
                                    AND ucp.collectionAmount >= 2
                                )
                                OR 
                                (
                                    (
                                        -- default playset size is three
                                        array_to_string(c.types, \',\') NOT LIKE \'%Hero%\' AND
                                        array_to_string(c.types, \',\') NOT LIKE \'%Equipment%\' AND
                                        array_to_string(c.types, \',\') NOT LIKE \'%Token%\' AND
                                        array_to_string(c.types, \',\') NOT LIKE \'%Weapon%\'
                                    )
                                    AND ucp.collectionAmount >= 3
                                )
                                OR 
                                (
                                    -- playsets of cards with the legendary keywords also just require one copy
                                    array_to_string(c.keywords, \',\') LIKE \'%Legendary%\' AND 
                                    ucp.collectionAmount >= 1
                                )
                            ')
                            ->getDQL()
                    )
                )
            );
            /** @var App\Entity\User $user */
            $user = $this->security->getUser();
            $qb->setParameter('userId', $user->getId()->toString());
        }

        $cards = $qb
            ->getQuery()
            ->getResult()
            ;
            
        return $cards;
    }
}
