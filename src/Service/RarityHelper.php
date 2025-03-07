<?php

namespace App\Service;

use App\Entity\Rarity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class RarityHelper
{
    private EntityManager $entityManager;

    private ArrayCollection $rarities;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;   
        $this->rarities = new ArrayCollection();
        $rarities = $this->entityManager->getRepository(Rarity::class)->findAll();

        foreach ($rarities as $rarity) {
            $this->rarities->set($rarity->getId(), $rarity->getDescription());
        }
    }

    public function getRarityDescriptionById(string $rarityId)
    {
        return $this->rarities[$rarityId];
    }
}
