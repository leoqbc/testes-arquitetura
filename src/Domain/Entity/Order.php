<?php

declare(strict_types=1);

namespace DifferDev\Domain\Entity;

use Exception;

final class Order
{
    public ?string $id = null;

    public float $discount = 0.0;

    /** @var list<OrderItem> */
    private readonly array $items;

    /**
     * @param array<OrderItem> $items
     * @throws Exception
     */
    public function __construct(array $items)
    {
        if ($items === []) {
            throw new Exception('Order must have items');
        }

        foreach ($items as $index => $item) {
            if (!$item instanceof OrderItem) {
                throw new Exception(
                    "Order item '$index' must be an instance of OrderItem"
                );
            }
        }
        $this->items = array_values($items);
    }

    /** @return list<OrderItem> */
    public function items(): array
    {
        return $this->items;
    }

    public function total(): float
    {
        $subtotal = 0.0;

        foreach ($this->items as $item) {
            $subtotal += $item->total();
        }

        $discountCalculator = new DiscountCalculator($this->discount);

        return $discountCalculator->calculate($subtotal);
    }

    public function canBeCreated(): void
    {
        // Regras de negócio aqui
        if ($this->total() <= 0) {
            throw new Exception('Order must have a total');
        }
    }
}
