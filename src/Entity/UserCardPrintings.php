<?php

namespace App\Entity;

use App\Repository\UserCardPrintingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserCardPrintingsRepository::class)]
class UserCardPrintings
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'cardPrintings')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private ?user $user = null;

    #[ORM\Id]
    #[ORM\ManyToOne()]
    #[ORM\JoinColumn(name: 'card_printing_unique_id', referencedColumnName: 'unique_id', nullable: false)]
    private CardPrinting $cardPrinting;

    #[ORM\Column]
    private ?int $collectionAmount = null;

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCardPrinting(): ?CardPrinting
    {
        return $this->cardPrinting;
    }

    public function setCardPrinting(?CardPrinting $cardPrinting): static
    {
        $this->cardPrinting = $cardPrinting;

        return $this;
    }

    public function getCollectionAmount(): ?int
    {
        return $this->collectionAmount;
    }

    public function setCollectionAmount(int $collectionAmount): static
    {
        $this->collectionAmount = $collectionAmount;

        return $this;
    }
}
