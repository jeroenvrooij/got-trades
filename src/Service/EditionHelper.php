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

    public function getEditionNameById(string $editionId)
    {
        return $this->editions[$editionId];
    }
}
