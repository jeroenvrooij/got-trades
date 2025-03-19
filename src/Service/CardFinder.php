<?php

namespace App\Service;

use App\Entity\CardPrinting;
use App\Entity\Set;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\SecurityBundle\Security;

class CardFinder
{
    private EntityManager $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        private Security $security,
    ) {
        $this->entityManager = $entityManager;    
    }

    /**
     * Finds all printings within a certain set and returns them grouped by unique cards
     * 
     * @param Set $set
     * 
     * @return ArrayCollection
     */
    public function findCardsBySetAndFoiling(Set $set, ?string $foiling)
    {
        $printings = $this->entityManager->getRepository(CardPrinting::class)->findBySetAndFoiling($set, $foiling);

        if (!$printings) {
            throw new EntityNotFoundException('No printings found in set by criteria');
        }

        $cards = new ArrayCollection();
        foreach ($printings as $printing) {
            if(!$cards->get($printing->getCard()->getUniqueId())) {
                $collection = new ArrayCollection(['card' => $printing->getCard(), 'printings' => new ArrayCollection()]);
                $cards->set($printing->getCard()->getUniqueId(), $collection);
            }
            $cards->get($printing->getCard()->getUniqueId())->get('printings')->add($printing);
        }
        
        return $cards;
    }
}
