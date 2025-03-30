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
        ?string $foiling = '', 
        ?bool $hideOwnedCards = false, 
        ?string $cardName = ''
    ) {
        if (FoilingHelper::NO_FILTER_KEY === $foiling) {
            $foiling = '';
        }
        
        $printings = $this->entityManager->getRepository(CardPrinting::class)->findBySet($set, $foiling, $hideOwnedCards, $cardName);

        $cards = new ArrayCollection();
        foreach ($printings as $printing) {
            if(!$cards->get($printing->getCard()->getUniqueId())) {
                $collection = new ArrayCollection(['card' => $printing->getCard(), 'printings' => new ArrayCollection()]);
                $cards->set($printing->getCard()->getUniqueId(), $collection);
            }
            $cards->get($printing->getCard()->getUniqueId())->get('printings')->add($printing);
        }
        
        return $cards;
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
        ?string $foiling = '', 
        ?bool $hideOwnedCards = false, 
        ?string $cardName = ''
    ) {
        if (FoilingHelper::NO_FILTER_KEY === $foiling) {
            $foiling = '';
        }
        
        $printings = $this->entityManager->getRepository(CardPrinting::class)->findByClass($className, $foiling, $hideOwnedCards, $cardName);
        
        $cards = new ArrayCollection();
        foreach ($printings as $printing) {
            if(!$cards->get($printing->getSet()->getName())) {
                $cards->set($printing->getSet()->getName(), new ArrayCollection());
            }
            $cardsFromSet = $cards->get($printing->getSet()->getName());
            if(!$cardsFromSet->get($printing->getCard()->getUniqueId())) {
                $collection = new ArrayCollection(['card' => $printing->getCard(), 'printings' => new ArrayCollection()]);
                $cardsFromSet->set($printing->getCard()->getUniqueId(), $collection);
            }
            $cardsFromSet->get($printing->getCard()->getUniqueId())->get('printings')->add($printing);
        }

        return $cards;
    }
}
