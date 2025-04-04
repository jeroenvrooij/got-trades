<?php

namespace App\Service;

use App\Entity\Card;
use App\Entity\Set;
use App\Entity\User;
use App\Entity\UserCardPrintings;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class UserCollectionManager
{
    private ArrayCollection $userCollectedPrintings;
    private ArrayCollection $userCollectedCards;
    private EntityManagerInterface $entityManager;
    
    // Avoid calling getUser() in the constructor: auth may not
    // be complete yet. Instead, store the entire Security object.
    public function __construct(
        private Security $security,
        EntityManagerInterface $entityManager,
    ) {
        $this->userCollectedPrintings = new ArrayCollection();
        $this->userCollectedCards = new ArrayCollection();
        $this->entityManager = $entityManager;
    }

    /**
     * Get's all collected card printings for logged in user
     */
    public function getCollectedPrintingsBy(User $user, ?Set $set = null, ?string $className = null): ArrayCollection
    {
        if ($this->userCollectedPrintings->isEmpty()) {
            $userCollectionModels = $this->entityManager->getRepository(UserCardPrintings::class)->getCollectionDataForUser($user, $set, $className);
            foreach ($userCollectionModels as $userCollectionModel) {
                $this->userCollectedPrintings->set($userCollectionModel->getCardPrintingUniqueId(), $userCollectionModel->getCollectionAmount());
            }
        }

        return $this->userCollectedPrintings;
    }

    /**
     * Get's all collected cards for logged in user
     */
    public function getCollectedCardsBy(User $user, ?Set $set = null, ?string $className = null): ArrayCollection
    {
        if ($this->userCollectedCards->isEmpty()) {
            $userCollectionModels = $this->entityManager->getRepository(UserCardPrintings::class)->getCollectionDataForUser($user, $set, $className);
            foreach ($userCollectionModels as $userCollectionModel) {
                $alreadyOwnedAmount = 0;
                if (null !== $this->userCollectedCards->get($userCollectionModel->getCardId())) {
                    $alreadyOwnedAmount = $this->userCollectedCards->get($userCollectionModel->getCardId());
                }

                $this->userCollectedCards->set(
                    $userCollectionModel->getCardId(), 
                    $userCollectionModel->getCollectionAmount() + $alreadyOwnedAmount
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
