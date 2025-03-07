<?php

namespace App\Service;

use App\Entity\Edition;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class EditionHelper
{
    private EntityManager $entityManager;

    private ArrayCollection $editions;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;   
        $this->editions = new ArrayCollection();
        $editions = $this->entityManager->getRepository(Edition::class)->findAll();

        foreach ($editions as $edition) {
            $this->editions->set($edition->getId(), $edition->getName());
        }
    }

    /**
     * As there is no FK from the card_printings to the editions table, this helper fetches all records once
     * so they can be used in Twig to convert the card_printings.edition value to the editions.name value. This
     * prevents a query being executed for each card printing.
     * 
     * @param string $editionId The id of the edition
     * 
     * @return string The name of the edition
     */
    public function getEditionNameById(string $editionId)
    {
        return $this->editions[$editionId];
    }
}
