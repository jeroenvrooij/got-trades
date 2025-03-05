<?php

namespace App\Entity;

use App\Repository\EditionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EditionRepository::class)]
#[ORM\Table(name: 'editions')]
class Edition
{
    #[ORM\Id]
    #[ORM\Column(length: 255, nullable: false)]
    private string $id;

    #[ORM\Column(length: 255, nullable: false)]
    private string $name;

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
