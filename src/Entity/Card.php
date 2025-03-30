<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardRepository::class)]
#[ORM\Table(name: 'cards')]
class Card
{
    #[ORM\Id]
    #[ORM\Column(length: 21, nullable: false)]
    private string $uniqueId;

    #[ORM\Column(length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(length: 10, nullable: false)]
    private string $pitch;
    
    #[ORM\OneToMany(targetEntity: CardPrinting::class, mappedBy: 'card')]
    private Collection $printings;
    
    #[ORM\Column(type: 'simple_array')]
    private array $types;
    
    #[ORM\Column(type: 'simple_array', name: 'card_keywords')]
    private array $keywords;
    

    #[ORM\Column(length: 50, nullable: true)]
    private string $className;

    public function __construct()
    {
        $this->printings = new ArrayCollection();
    }

    public function getUniqueId(): ?string
    {
        return $this->uniqueId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPitch(): ?string
    {
        return $this->pitch;
    }

    /**
     * @return ArrayCollection<int, CardPrinting>
     */
    public function getPrintings(): Collection
    {
        return $this->printings;
    }

    // public function getPrintingsFromSet(string $setId): Collection
    // {
    //     $criteria = Criteria::create()
    //         ->andWhere(Criteria::expr()->eq('setId', $setId));
    //     return $this->getPrintings()->matching($criteria);
    // }

    public function getTypes(): array 
    { 
        // trim the curly brackets because Postgres
        return array_map(fn($type) => trim($type, '{}'), $this->types);
    }

    public function getKeywords(): array 
    { 
        // trim the curly brackets because Postgres
        return array_map(fn($keyword) => trim($keyword, '{}'), $this->keywords);
    }

    public function getClassName(): string 
    { 
        return $this->className;
    }
}
