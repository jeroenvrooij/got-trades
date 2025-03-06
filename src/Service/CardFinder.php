<?php

namespace App\Service;

use App\Entity\Card;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class CardFinder
{
    private EntityManager $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;    
    }

    public function findCardsBySet()
    {
        $cards = $this->entityManager->getRepository(Card::class)->findBySet('ROS');

        if (!$cards) {
            throw new EntityNotFoundException('No cards found in set by criteria');
        }

        return $cards;
    }
}
