<?php

namespace App\Entity;

use App\Repository\SetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SetRepository::class)]
#[ORM\Table(name: 'sets')]
class Set
{
    #[ORM\Column(length: 21, nullable: false)]
    private string $uniqueId;
    
    #[ORM\Id]
    #[ORM\Column(length: 255, nullable: false)]
    private string $id;

    #[ORM\Column(length: 255, nullable: false)]
    private string $name;

    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
