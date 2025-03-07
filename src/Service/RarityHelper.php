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

    /**
     * As there is no FK from the card_printings to the rarities table, this helper fetches all records once
     * so they can be used in Twig to convert the card_printings.rarity value to the rarities.description value. This
     * prevents a query being executed for each card printing.
     * 
     * @param string $rarityId The id of the rarity
     * 
     * @return string The description of the rarity
     */
    public function getRarityDescriptionById(string $rarityId)
    {
        return $this->rarities[$rarityId];
    }
}
