<?php

namespace App\Service;

use App\Entity\CardPrinting;
use App\Entity\Set;
use App\Model\CardPrintingsResultSet;
use App\Repository\CardPrintingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\SecurityBundle\Security;

class CardFinder
{
    private EntityManager $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        private Security $security,
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * Finds all printings within a certain set and returns the first batch (using Paginator
     */
    public function findPaginatedCardsBySet(
        ?Set $set,
        int $offset = 0,
        ?bool $hideOwnedCards = false,
        ?bool $collectorView = false,
        ?string $foiling = '',
        ?string $cardName = '',
        ?string $rarity = '',
    ): CardPrintingsResultSet
    {
        if (FoilingHelper::NO_FILTER_KEY === $foiling) {
            $foiling = '';
        }

        if (RarityHelper::NO_FILTER_KEY === $rarity) {
            $rarity = '';
        }

        $paginator = $this->entityManager->getRepository(CardPrinting::class)->findPaginatedBySet($set, $offset, $hideOwnedCards, $collectorView, $foiling, $cardName, $rarity);

        return $this->buildCardPrintingsResultSet($paginator, $collectorView, $offset);
    }

    /**
     * Finds all printings belonging to a certain class and returns the first batch (using Paginator)
     */
    public function findPaginatedCardsByClass(
        ?string $className,
        ?int $offset = 0,
        ?bool $hideOwnedCards = false,
        ?bool $collectorView = false,
        ?string $foiling = '',
        ?string $cardName = '',
        ?string $rarity = '',
    ): CardPrintingsResultSet
    {
        if (FoilingHelper::NO_FILTER_KEY === $foiling) {
            $foiling = '';
        }

        if (RarityHelper::NO_FILTER_KEY === $rarity) {
            $rarity = '';
        }

        $paginator = $this->entityManager->getRepository(CardPrinting::class)->findPaginatedByClass($className, $offset, $hideOwnedCards, $collectorView, $foiling, $cardName, $rarity);

        return $this->buildCardPrintingsResultSet($paginator, $collectorView, $offset);
    }

    /**
     * Finds all printings belonging to a certain class
     */
    public function findPaginatedPromos(
        ?int $offset = 0,
        ?bool $hideOwnedCards = false,
        ?string $foiling = '',
        ?string $cardName = '',
    ): CardPrintingsResultSet
    {
        if (FoilingHelper::NO_FILTER_KEY === $foiling) {
            $foiling = '';
        }

        $paginator = $this->entityManager->getRepository(CardPrinting::class)->findPaginatedPromos($offset, $hideOwnedCards, $foiling, $cardName);

        return $this->buildCardPrintingsResultSet($paginator, true, $offset);
    }

    /**
     * Finds all printings belonging to a certain class
     */
    public function findPaginatedByCardName(
        ?string $cardName = '',
        ?int $offset = 0,
        ?string $foiling = '',
        ?string $rarity = '',
    ): CardPrintingsResultSet
    {
        if (FoilingHelper::NO_FILTER_KEY === $foiling) {
            $foiling = '';
        }
        if (RarityHelper::NO_FILTER_KEY === $rarity) {
            $rarity = '';
        }
        if ('' === $cardName || null === $cardName) {
            return new CardPrintingsResultSet(new ArrayCollection(), -1, 0);
        }

        $paginator = $this->entityManager->getRepository(CardPrinting::class)->findPaginatedByCardName($cardName, $offset, $foiling, $rarity);

        return $this->buildCardPrintingsResultSet($paginator, true, $offset);
    }

    /**
     * Takes the Paginator and depending on the type of view (player/collector) builds
     * the correct result set.
     * When in player view, the cards are grouped by cardPrinting.cardId, which affects pagination. In order
     * for that to keep on working we first fetch all the card ids (paginated) and then fetch all the printings
     */
    private function buildCardPrintingsResultSet(
        Paginator $paginator,
        bool $collectorView = false,
        int $offset = 0,
    ): CardPrintingsResultSet
    {
        if ($collectorView) {
            return new CardPrintingsResultSet(
                $this->buildPrintingTree(iterator_to_array($paginator)),
                count($paginator),
                min(count($paginator), $offset + CardPrintingRepository::CARDS_PER_PAGE),
            );
        } else {
            $paginatedCardIds = [];
            foreach ($paginator as $printing) {
                if (!in_array($printing->getCardId(), $paginatedCardIds)) {
                    $paginatedCardIds[] = $printing->getCardId();
                }
            }

            $cardPrintings = $this->entityManager->getRepository(CardPrinting::class)->findByCardIds($paginatedCardIds);

            return new CardPrintingsResultSet(
                $this->buildPrintingTree($cardPrintings),
                count($paginator),
                min(count($paginator), $offset + count($cardPrintings)),
            );
        }
    }

    /**
     * Takes an Paginator containing card printings and build an ArrayCollection tree structured like:
     *
     * [%SET_ID%] => [
     *  [%CARD_ID%] => [
     *          'card' => App\Entity\Card,
     *          'printings => <ArrayCollection> App\Entity\CardPrinting
     *  ]
     * ]
     */
    private function buildPrintingTree(array $cardPrintings): ArrayCollection
    {
        $cards = new ArrayCollection();
        foreach ($cardPrintings as $printing)
        {
            if(!$cards->get($printing->getSet()->getName())) {
                $cards->set($printing->getSet()->getName(), new ArrayCollection());
            }
            $cardsFromSet = $cards->get($printing->getSet()->getName());
            if(!$cardsFromSet->get($printing->getCardId())) {
                $collection = new ArrayCollection(['card' => $printing->getCard(), 'printings' => new ArrayCollection()]);
                $cardsFromSet->set($printing->getCardId(), $collection);
            }
            $cardsFromSet->get($printing->getCardId())->get('printings')->add($printing);
        }

        return $cards;
    }
}
