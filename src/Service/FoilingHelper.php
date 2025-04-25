<?php

namespace App\Service;

use App\Entity\Foiling;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class FoilingHelper
{
    public const PLACEHOLDER_KEY = '%foiling_filter%';
    public const PLACEHOLDER_DESC = 'Filter on foiling';

    public const NO_FILTER_KEY = 'no_filter';
    public const NO_FILTER_DESC = 'No filter';

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

    /**
     * As there is no FK from the card_printings to the foilings table, this helper fetches all records once
     * so they can be used in Twig to convert the card_printings.foiling value to the foilings.name value. This
     * prevents a query being executed for each card printing.
     *
     * @param string $foilingId The id of the foiling
     *
     * @return string The name of the foiling
     */
    public function getFoilingNameById(string $foilingId)
    {
        return $this->foilings[$foilingId];
    }

    public function getAllFoilings(): ArrayCollection
    {
        return $this->foilings;
    }
}
