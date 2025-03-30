<?php

namespace App\Entity;

use App\Repository\CardPrintingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardPrintingRepository::class)]
#[ORM\Table(name: 'card_printings')]
class CardPrinting
{
    #[ORM\Id]
    #[ORM\Column(length: 15, nullable: false)]
    private string $className;
    
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Card::class, inversedBy: 'className')]
    #[ORM\JoinColumn(name: 'card_unique_id', referencedColumnName: 'unique_id', nullable: false)]
    private Card $card;

    public function getCard(): Card
    {
        return $this->card;
    }

    public function getClassName(): string
    {
        return $this->className;
    }
}
