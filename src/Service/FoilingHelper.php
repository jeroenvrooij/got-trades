<?php

namespace App\Service;

use App\Entity\Foiling;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class FoilingHelper
{
    private EntityManager $entityManager;

    private ArrayCollection $foilings;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;   
        $this->foilings = new ArrayCollection();
        $foilings = $this->entityManager->getRepository(Foiling::class)->findAll();

        foreach ($foilings as $foiling) {
            $this->foilings->set($foiling->getId(), $foiling->getName());
        }
    }

    public function getFoilingNameById(string $foilingId)
    {
        return $this->foilings[$foilingId];
    }
}
