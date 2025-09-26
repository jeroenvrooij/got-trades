<?php

namespace App\Entity;

use App\Repository\RarityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RarityRepository::class)]
#[ORM\Table(name: 'rarities')]
class Rarity
{
    public const PROMO = 'P';

    #[ORM\Id]
    #[ORM\Column(length: 255, nullable: false)]
    private string $id;

    #[ORM\Column(length: 255, nullable: false)]
    private string $description;

    public function getId(): string
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
