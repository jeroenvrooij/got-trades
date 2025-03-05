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

    #[ORM\ManyToOne(targetEntity: Card::class, inversedBy: 'printings')]
    #[ORM\JoinColumn(name: 'card_unique_id', referencedColumnName: 'unique_id', nullable: false)]
    private Card $card;

    #[ORM\ManyToOne(targetEntity: Edition::class)]
    #[ORM\JoinColumn(name: 'edition', referencedColumnName: 'id', nullable: false)]
    private Edition $edition;

    #[ORM\ManyToOne(targetEntity: Foiling::class)]
    #[ORM\JoinColumn(name: 'foiling', referencedColumnName: 'id', nullable: false)]
    private Foiling $foiling;
    
    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    public function getCardId(): string
    {
        return $this->cardId;
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

    public function getEdition(): Edition
    {
        return $this->edition;
    }

    public function getFoiling(): Foiling
    {
        return $this->foiling;
    }
}
