<?php

namespace App\Entity;

use App\Repository\CardFaceAssociationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardFaceAssociationRepository::class)]
#[ORM\Table(name: 'card_face_associations')]
class CardFaceAssociation
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: CardPrinting::class)]
    #[ORM\JoinColumn(name: 'front_unique_id', referencedColumnName: 'unique_id', nullable: false)]
    private CardPrinting $frontCardPrinting;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: CardPrinting::class)]
    #[ORM\JoinColumn(name: 'back_unique_id', referencedColumnName: 'unique_id', nullable: false)]
    private CardPrinting $backCardPrinting;

    #[ORM\Column(name: 'is_DFC', nullable: false)]
    private bool $isDoubleFacedCard;

    public function getFrontCardPrinting(): CardPrinting
    {
        return $this->frontCardPrinting;
    }

    public function setFrontCardPrinting(CardPrinting $cardPrinting): self
    {
        $this->frontCardPrinting = $cardPrinting;

        return $this;
    }   
    
    public function getBackCardPrinting(): CardPrinting
    {
        return $this->backCardPrinting;
    }

    public function setBackCardPrinting(CardPrinting $cardPrinting): self
    {
        $this->backCardPrinting = $cardPrinting;

        return $this;
    }

    public function isDoubleFacedCard(): bool
    {
        return $this->isDoubleFacedCard;
    }

    public function setIsDoubleFacedCard(bool $isDoubleFacedCard): self
    {
        $this->isDoubleFacedCard = $isDoubleFacedCard;

        return $this;
    }
}
