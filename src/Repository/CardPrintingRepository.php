<?php

namespace App\Repository;

use App\Entity\CardFaceAssociation;
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
    private CardFaceAssociationRepository $cardFaceAssociationRepository;

    public function __construct(ManagerRegistry $registry, CardFaceAssociationRepository $cardFaceAssociationRepository)
    {
        parent::__construct($registry, CardPrinting::class);
        $this->cardFaceAssociationRepository = $cardFaceAssociationRepository;
    }

    /**
    * @return CardPrinting[] Returns an array of CardPrinting objects
    */
    public function findBySetAndFoiling(Set $set, ?string $foiling): array
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

        return $qb
            ->getQuery()
            ->getResult()
            ;   
    }
}
