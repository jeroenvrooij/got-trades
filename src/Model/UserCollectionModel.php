<?php

namespace App\Model;

class UserCollectionModel
{
    private string $cardPrintingUniqueId;
    private int $collectionAmount;
    private string $cardId;

    public function __construct(
        string $uniqueId,
        string $cardId,
        int $collectionAmount,
    ) {
        $this->cardPrintingUniqueId = $uniqueId;
        $this->collectionAmount = $collectionAmount;
        $this->cardId = $cardId;
    }

    public function getCardPrintingUniqueId(): string
    {
        return $this->cardPrintingUniqueId;
    }

    public function getCardId(): string
    {
        return $this->cardId;
    }

    public function getCollectionAmount(): int
    {
        return $this->collectionAmount;
    }
}
