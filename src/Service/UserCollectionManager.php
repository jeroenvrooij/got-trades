<?php

namespace App\Service;

use App\Entity\Card;
use App\Entity\User;
use App\Entity\UserCardPrintings;
use App\Model\CardPrintingsResultSet;
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
    public function getCollectedPrintingsBy(
        User $user,
        CardPrintingsResultSet $cardPrintingsResultSet
    ): ArrayCollection
    {
        if ($this->userCollectedPrintings->isEmpty()) {
            $userCollectionModels = $this->entityManager->getRepository(UserCardPrintings::class)->getCollectionDataForPrintingsByUser($user, $cardPrintingsResultSet);
            foreach ($userCollectionModels as $userCollectionModel) {
                $this->userCollectedPrintings->set($userCollectionModel->getCardPrintingUniqueId(), $userCollectionModel->getCollectionAmount());
            }
        }

        return $this->userCollectedPrintings;
    }

    /**
     * Get's all collected cards for logged in user
     */
    public function getCollectedCardsBy(
        User $user,
        CardPrintingsResultSet $cardPrintingsResultSet
    ): ArrayCollection
    {
        if ($this->userCollectedCards->isEmpty()) {
            $userCollectionModels = $this->entityManager->getRepository(UserCardPrintings::class)->getCollectionDataForPrintingsByUser($user, $cardPrintingsResultSet);
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
}
