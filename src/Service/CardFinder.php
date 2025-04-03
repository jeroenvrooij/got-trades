<?php

namespace App\Service;

use App\Entity\CardPrinting;
use App\Entity\Set;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
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
     * Finds all printings within a certain set and returns them grouped by unique cards
     * 
     * @param Set $set
     * 
     * @return ArrayCollection
     */
    public function findCardsBySet(
        ?Set $set, 
        ?bool $hideOwnedCards = false, 
        ?bool $collectorView = false,
        ?string $foiling = '', 
        ?string $cardName = '',
    ) {
        if (FoilingHelper::NO_FILTER_KEY === $foiling) {
            $foiling = '';
        }
        
        $cardPrintings = $this->entityManager->getRepository(CardPrinting::class)->findBySet($set, $hideOwnedCards, $collectorView, $foiling, $cardName);

        return $this->buildPrintingTree($cardPrintings);
    }

    /**
     * Finds all printings belonging to a certain class and returns them grouped by unique cards
     * 
     * @param string $className
     * 
     * @return ArrayCollection
     */
    public function findCardsByClass(
        ?string $className, 
        ?bool $hideOwnedCards = false, 
        ?bool $collectorView = false,
        ?string $foiling = '', 
        ?string $cardName = '',
    ) {
        if (FoilingHelper::NO_FILTER_KEY === $foiling) {
            $foiling = '';
        }
        
        $cardPrintings = $this->entityManager->getRepository(CardPrinting::class)->findByClass($className, $hideOwnedCards, $collectorView, $foiling, $cardName);
        
        $cardPrintings = $this->buildPrintingTree($cardPrintings);
        $printingsOrderedBySet = $this->orderPrintingTreeBySet($cardPrintings);

        return $printingsOrderedBySet;
    }

    /**
     * Takes an array of card printings and build a tree structured like:
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
        foreach ($cardPrintings as $printing) {
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

    /**
     * Orders the tree, moving booster sets up in order the came out. All other sets, 
     * like armory decks will be added last.
     */
    private function orderPrintingTreeBySet(?ArrayCollection $cardPrintings): ArrayCollection
    {
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
        $printingsOrderedBySet = new ArrayCollection();
        
        foreach ($desiredSetOrder as $key) {
            if (null !== $cardPrintings->get($key)) {
                $printingsOrderedBySet->set($key, $cardPrintings->get($key));
            }
        }

        // Add all elements that where not ordered
        foreach ($cardPrintings as $setKey => $setContents) {
            if (null === $printingsOrderedBySet->get($setKey)) {
                $printingsOrderedBySet->set($setKey, $setContents);
            }
        }

        return $printingsOrderedBySet;
    }
}
