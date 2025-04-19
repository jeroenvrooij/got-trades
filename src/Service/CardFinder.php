<?php

namespace App\Service;

use App\Entity\CardPrinting;
use App\Entity\Set;
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
     *
     * @param Set $set
     *
     * @return Paginator
     */
    public function findPaginatedCardsBySet(
        ?Set $set,
        int $offset = 0,
        ?bool $hideOwnedCards = false,
        ?bool $collectorView = false,
        ?string $foiling = '',
        ?string $cardName = '',
    ): Paginator
    {
        if (FoilingHelper::NO_FILTER_KEY === $foiling) {
            $foiling = '';
        }

        return $this->entityManager->getRepository(CardPrinting::class)->findPaginatedBySet($set, $offset, $hideOwnedCards, $collectorView, $foiling, $cardName);
    }

    /**
     * Finds all printings belonging to a certain class and returns the first batch (using Paginator)
     *
     * @param string $className
     *
     * @return Paginator
     */
    public function findPaginatedCardsByClass(
        ?string $className,
        ?int $offset = 0,
        ?bool $hideOwnedCards = false,
        ?bool $collectorView = false,
        ?string $foiling = '',
        ?string $cardName = '',
    ): Paginator
    {
        if (FoilingHelper::NO_FILTER_KEY === $foiling) {
            $foiling = '';
        }

        return $this->entityManager->getRepository(CardPrinting::class)->findPaginatedByClass($className, $offset, $hideOwnedCards, $collectorView, $foiling, $cardName);
    }

    /**
     * Finds all printings belonging to a certain class
     *
     * @param string $className
     *
     * @return Paginator
     */
    public function findPaginatedPromos(
        ?int $offset = 0,
        ?bool $hideOwnedCards = false,
        ?string $foiling = '',
        ?string $cardName = '',
    ): Paginator
    {
        if (FoilingHelper::NO_FILTER_KEY === $foiling) {
            $foiling = '';
        }

        return $this->entityManager->getRepository(CardPrinting::class)->findPaginatedPromos($offset, $hideOwnedCards, $foiling, $cardName);
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
    public function buildPrintingTree(Paginator $cardPrintings): ArrayCollection
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
