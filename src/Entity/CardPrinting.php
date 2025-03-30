<?php

namespace App\Entity;

use App\Repository\CardPrintingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardPrintingRepository::class)]
#[ORM\Table(name: 'card_printings')]
class CardPrinting
{
    #[ORM\Id]
    #[ORM\Column(length: 21, nullable: false)]
    private string $uniqueId;

    #[ORM\Column(length: 15, nullable: false)]
    private string $cardId;

    // #[ORM\Column(length: 15, nullable: false)]
    // private string $setId;

    #[ORM\ManyToOne(targetEntity: Set::class)]
    #[ORM\JoinColumn(name: 'set_id', referencedColumnName: 'id', nullable: false)]
    private Set $set;


    #[ORM\ManyToOne(targetEntity: Card::class, inversedBy: 'printings')]
    #[ORM\JoinColumn(name: 'card_unique_id', referencedColumnName: 'unique_id', nullable: false)]
    private Card $card;

    #[ORM\Column(length: 15, nullable: false)]
    private string $edition;

    #[ORM\Column(length: 15, nullable: false)]
    private string $foiling;

    #[ORM\Column(length: 15, nullable: false)]
    private string $rarity;

    #[ORM\Column(length: 15, nullable: false)]
    private string $artVariations;
    
    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    public function getCardId(): string
    {
        return $this->cardId;
    }

    public function getSet(): Set
    {
        return $this->set;
    }

    public function getCard(): Card
    {
        return $this->card;
    }

    public function setCard(Card $card): self
    {
        $this->card = $card;

        return $this;
    }

    public function getEdition(): string
    {
        return $this->edition;
    }

    public function getFoiling(): string
    {
        return $this->foiling;
    }

    public function getRarity(): string
    {
        return $this->rarity;
    }

    public function getArtVariations(): string
    {
        return $this->artVariations;
    }
}
