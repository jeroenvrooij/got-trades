<?php

namespace App\Repository;

use App\Entity\Card;
use App\Entity\CardPrinting;
use App\Entity\Set;
use App\Entity\UserCardPrintings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<CardPrinting>
 */
class CardPrintingRepository extends ServiceEntityRepository
{
    public const CARDS_PER_PAGE = 50;

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
    * @return Paginator Returns a Paginator containting CardPrinting objects
    */
    public function findPaginatedBySet(
        Set $set,
        int $offset = 0,
        ?bool $hideOwnedCards = false,
        ?bool $collectorView = false,
        ?string $foiling = '',
        ?string $cardName = '',
        ?string $rarity = '',
    ): Paginator
    {
        $qb = $this->buildCoreQuery($hideOwnedCards, $collectorView, $foiling, $cardName, $rarity);

        $qb
            ->andWhere('s.id = :setId')
            ->setParameter('setId', $set->getId())
            ->setMaxResults(self::CARDS_PER_PAGE)
            ->setFirstResult($offset)
        ;

        return new Paginator($qb->getQuery());
    }

    /**
    * @return Paginator Returns a Paginator containing CardPrinting objects
    */
    public function findPaginatedByClass(
        ?string $className,
        ?int $offset = 0,
        ?bool $hideOwnedCards = false,
        ?bool $collectorView = false,
        ?string $foiling = '',
        ?string $cardName = '',
        ?string $rarity = '',
    ): Paginator
    {
        $qb = $this->buildCoreQuery($hideOwnedCards, $collectorView, $foiling, $cardName, $rarity);

        $qb
            ->innerJoin('c.cardClasses', 'classes')
            ->andWhere('classes.className = :className')
            ->setParameter('className', ucfirst($className))
            ->andWhere('cp.rarity != :promo')
            ->setParameter('promo', 'P')
            ->setMaxResults(self::CARDS_PER_PAGE)
            ->setFirstResult($offset)
        ;

        return new Paginator($qb->getQuery());
    }

    /**
    * @return Paginator Returns a Paginator containing CardPrinting objects
    */
    public function findPaginatedByCardName(
        string $cardName,
        ?int $offset = 0,
        ?string $foiling = '',
        ?string $rarity = '',
    ): Paginator
    {
        $qb = $this->buildCoreQuery(false, true, $foiling, $cardName, $rarity);

        $qb
            ->setMaxResults(self::CARDS_PER_PAGE)
            ->setFirstResult($offset)
        ;

        return new Paginator($qb->getQuery());
    }

    /**
    * @return Paginator Paginator containing CardPrinting objects
    */
    public function findPaginatedPromos(
        ?int $offset = 0,
        ?bool $hideOwnedCards = false,
        ?string $foiling = '',
        ?string $cardName = '',
    ): Paginator
    {
        $qb = $this->buildCoreQuery($hideOwnedCards, true, $foiling, $cardName);

        $qb
            ->andWhere('cp.rarity = :promo')
            ->setParameter('promo', 'P')
            ->setMaxResults(self::CARDS_PER_PAGE)
            ->setFirstResult($offset)
        ;

        $cardsQuery = $qb
            ->getQuery()
            ;

        return new Paginator($cardsQuery);
    }

    /**
     * Find all cardPrintings with matching the cardId from the collection of $cardIds.
     *
     * Used for collecting paginated subsets (in player view).
     *
     * Applies the same ordering as the core query, so paginated results match.
     *
     * Not matching on class or set, because (until now) LSS does not print the same
     * card id in another set or class. Eg: WTR191 is Scar for a Scar from WTR. WTR191 will
     * not be in another set. Scar for a Scar is, but then it will have a different card id (UPR209).
     */
    public function findByCardIds(array $cardIds)
    {
        $qb = $this->startQueryBuilder();

        $qb
            ->andWhere('cp.cardId IN (:cardIds)')
            ->setParameter('cardIds', $cardIds)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Builds the QueryBuilder object with the core query for fetching card printings.
     * Applies filters, depending on the current view (player/collector)
     */
    private function buildCoreQuery(
        ?bool $hideOwnedCards = false,
        ?bool $collectorView = false,
        ?string $foiling = '',
        ?string $cardName = '',
        ?string $rarity = '',
    ): QueryBuilder
    {
        $qb = $this->startQueryBuilder();

        if($foiling) {
            $qb
                ->andWhere('cp.foiling = :foiling')
                ->setParameter('foiling', $foiling)
                ;
        }

        if ($rarity) {
            $qb
                ->andWhere('cp.rarity = :rarity')
                ->setParameter('rarity', $rarity)
            ;
        }

        if($cardName) {
            $qb
                ->andWhere('LOWER(c.name) LIKE :cardName')
                ->setParameter('cardName', '%' . strtolower($cardName) . '%')
                ;
        }

        if ($hideOwnedCards) {
            /** @var App\Entity\User $user */
            $user = $this->security->getUser();
            $qb->setParameter('userId', $user->getId()->toString());

            if ($collectorView) {
                $qb
                    ->leftJoin(UserCardPrintings::class, 'ucp', Join::WITH, 'ucp.cardPrinting = cp.uniqueId AND ucp.user = :userId')
                    ->andWhere(
                        $qb->expr()->orX(
                            'ucp.cardPrinting IS NULL', // User has no copies yet
                            'ucp.collectionAmount < c.playsetSize' // Not enough copies collected
                        )
                    )
                ;
            } else {
                // Player view: group across all printings for the same card (cardId)
                $qb->andWhere(
                    // When hiding completed playsets the card printing should not exists in this subsets of completed playsets
                    $qb->expr()->not(
                        $qb->expr()->exists(
                            $this->userCardPrintingsRepository->createQueryBuilder('ucp')
                                ->select('1')
                                ->innerJoin(CardPrinting::class, 'ucp_cp', Join::WITH, 'ucp.cardPrinting = ucp_cp.uniqueId')
                                ->where('ucp.user = :userId')
                                ->andWhere('ucp_cp.card = c')
                                ->groupBy('ucp_cp.card')
                                ->having('SUM(ucp.collectionAmount) >= c.playsetSize')
                                ->getDQL()
                        )
                    )
                );
            }
        }

        return $qb;
    }

    /**
     * Create the QueryBuilder object, applying the correct select, inner joins and ordering. Also filters
     * out the double sized printings.
     */
    private function startQueryBuilder()
    {
        $qb = $this->createQueryBuilder('cp');

        $qb
            ->select('cp, c, s')
            ->innerJoin('cp.card', 'c')
            ->innerJoin('cp.set', 's')
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
        );

        // 🧩 Add custom ordering logic
        $desiredSetOrder = [
            'The Hunted',
            'Rosetta',
            'Part the Mistveil',
            'Heavy Hitters',
            'Bright Lights',
            'Dusk till Dawn',
            'Outsiders',
            'Dynasty',
            'History Pack 1',
            'Uprising',
            'Everfest',
            'Tales of Aria',
            'Monarch',
            'Crucible of War',
            'Arcane Rising',
            'Welcome to Rathe',
        ];

        $orderCase = 'CASE s.name ';
        foreach ($desiredSetOrder as $index => $setName) {
            $orderCase .= sprintf("WHEN '%s' THEN %d ", addslashes($setName), $index);
        }
        $orderCase .= 'ELSE 999 END';

        $qb
            ->addSelect("($orderCase) AS HIDDEN setOrder")
            ->addOrderBy('setOrder', 'ASC')
            ->addOrderBy('cp.cardId', 'ASC')
            ->addOrderBy('cp.edition', 'DESC')
            ->addOrderBy('cp.foiling', 'DESC')
            ->addOrderBy('cp.artVariations', 'DESC')
        ;

        return $qb;
    }
}
