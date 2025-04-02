<?php

namespace App\Service;

use App\Entity\Card;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\SecurityBundle\Security;

class UserCollectionManager
{
    private ArrayCollection $userCollectedPrintings;
    private ArrayCollection $userCollectedCards;
    
    // Avoid calling getUser() in the constructor: auth may not
    // be complete yet. Instead, store the entire Security object.
    public function __construct(
        private Security $security,
    ) {
        $this->userCollectedPrintings = new ArrayCollection();
        $this->userCollectedCards = new ArrayCollection();
    }

    /**
     * Get's all collected card printings for logged in user
     */
    public function getAllCollectedPrintingsFromLoggedInUser(): ArrayCollection
    {
        if ($this->userCollectedPrintings->isEmpty()) {
            /** @var App\Entity\User $user */
            $user = $this->security->getUser();
        
            foreach ($user->getCardPrintings() as $userCardPrinting) {
                $this->userCollectedPrintings->set($userCardPrinting->getCardPrinting()->getUniqueId(), $userCardPrinting->getCollectionAmount());
            }
        }

        return $this->userCollectedPrintings;
    }

    /**
     * Get's all collected cards for logged in user
     */
    public function getAllCollectedCardsFromLoggedInUser(): ArrayCollection
    {
        if ($this->userCollectedCards->isEmpty()) {
            /** @var App\Entity\User $user */
            $user = $this->security->getUser();
        
            foreach ($user->getCardPrintings() as $userCardPrinting) {
                $alreadyOwnedAmount = 0;
                if (null !== $this->userCollectedCards->get($userCardPrinting->getCardPrinting()->getCardId())) {
                    $alreadyOwnedAmount = $this->userCollectedCards->get($userCardPrinting->getCardPrinting()->getCardId());
                }

                $this->userCollectedCards->set(
                    $userCardPrinting->getCardPrinting()->getCardId(), 
                    $userCardPrinting->getCollectionAmount() + $alreadyOwnedAmount
                );
            }
        }

        return $this->userCollectedCards;
    }

    public function getPlaysetSizeForCard(Card $card): int
    {
        // cards of these certain types have a smaller playset
        $smallTypes = ['Demi-Hero', 'Hero', 'Equipment', 'Token', 'Weapon'];
        foreach ($card->getTypes() as $type) {
            if (in_array($type, $smallTypes)) {
                if('Weapon' === $type && in_array('1H', $card->getTypes())) {
                    // one hander weapons playset consists of 2
                    return 2;
                } 
                return 1;
            }
        }
        // cards with the 'Legendary' keyword can only be 'one of'
        if (in_array('Legendary', $card->getKeywords())) {
            return 1;
        }

        // default playset size == 3
        return 3;
    }
}
