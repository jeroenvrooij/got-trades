<?php

namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;

class CardPrintingsViewModel
{
    private ArrayCollection $cardPrintings;
    private int $totalAmount;
    private int $nextOffset;

    public function __construct(
        ArrayCollection $cardPrintings,
        int $totalAmount,
        int $nextOffset,
    ) {
        $this->cardPrintings = $cardPrintings;
        $this->totalAmount = $totalAmount;
        $this->nextOffset = $nextOffset;
    }

    public function getCardPrintings(): ArrayCollection
    {
        return $this->cardPrintings;
    }

    public function getTotalAmount(): int
    {
        return $this->totalAmount;
    }

    public function getNextOffset(): int
    {
        return $this->nextOffset;
    }
}
