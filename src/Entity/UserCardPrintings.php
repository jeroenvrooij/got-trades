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
    #[ORM\Column(length: 21, nullable: false)]
    private string $cardPrintingUniqueId;

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

    public function getCardPrintingUniqueId(): ?string
    {
        return $this->cardPrintingUniqueId;
    }

    public function setCardPrintingUniqueId(?string $cardPrintingUniqueId): static
    {
        $this->cardPrintingUniqueId = $cardPrintingUniqueId;

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
