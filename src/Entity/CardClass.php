<?php

namespace App\Entity;

use App\Repository\CardClassRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardClassRepository::class)]
#[ORM\Table(name: 'card_class')]
class CardClass
{
    #[ORM\Id]
    #[ORM\Column(nullable: false)]
    private int $id;

    #[ORM\Column(length: 15, nullable: false)]
    private string $className;
    
    #[ORM\ManyToOne(targetEntity: Card::class, inversedBy: 'cardClasses')]
    #[ORM\JoinColumn(name: 'card_unique_id', referencedColumnName: 'unique_id', nullable: false)]
    private Card $card;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCard(): Card
    {
        return $this->card;
    }

    public function getClassName(): string
    {
        return $this->className;
    }
}
