<?php

namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\SecurityBundle\Security;

class UserCollectionManager
{
    private ArrayCollection $userCollectedCards;

    // Avoid calling getUser() in the constructor: auth may not
    // be complete yet. Instead, store the entire Security object.
    public function __construct(
        private Security $security,
    ) {
        $this->userCollectedCards = new ArrayCollection();
    }

    public function getAllCollectedCardsFromLoggedInUser(): ArrayCollection
    {
        if ($this->userCollectedCards->isEmpty()) {
            /** @var App\Entity\User $user */
            $user = $this->security->getUser();
        
            foreach ($user->getCardPrintings() as $userCardPrinting) {
                $this->userCollectedCards->set($userCardPrinting->getCardPrintingUniqueId(), $userCardPrinting->getCollectionAmount());
            }
        }

        return $this->userCollectedCards;

    }
}
