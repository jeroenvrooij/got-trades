<?php

namespace App\Entity;

use App\Repository\FoilingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FoilingRepository::class)]
#[ORM\Table(name: 'foilings')]
class Foiling
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
