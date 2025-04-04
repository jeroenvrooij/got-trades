<?php

namespace App\Entity;

use App\Repository\UserCardPrintingsRepository;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserCardPrintingsRepository::class)]
class UserCardPrintings
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'cardPrintings')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private ?user $user = null;

    // This can't be a relation as Doctrine would then make a FK, which would cascase delete records when
    // card db is recreated
    #[ORM\Id]
    #[ORM\Column(name: 'card_printing_unique_id', length: 21, nullable: false)]
    private string $cardPrinting;

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

    public function getCardPrinting(): ?string
    {
        return $this->cardPrinting;
    }

    public function setCardPrinting(?string $cardPrinting): static
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
