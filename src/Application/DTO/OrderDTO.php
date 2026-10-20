<?php

namespace DifferDev\Application\DTO;

use DifferDev\Domain\Entity\Order;
use DifferDev\Domain\Entity\OrderItem;

class OrderDTO
{
    public array $items;

    public function setItem(float $price, float $quantity, ?string $id = null): void
    {
        $this->items[] = new OrderItem($price, $quantity);
    }
}
