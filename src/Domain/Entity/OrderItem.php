<?php

declare(strict_types=1);

namespace DifferDev\Domain\Entity;

use InvalidArgumentException;

final class OrderItem
{
    /**
     * @throws \Exception
     */
    public function __construct(
        public readonly float $price,
        public readonly int $quantity,
        public ?string $id = null,
    ) {
        if ($price < 0) {
            throw new \Exception('Order item must have a non-negative price');
        }
        if ($quantity < 1) {
            throw new \Exception('Order item must have a positive quantity');
        }
        if (is_infinite($price)) {
            throw new \Exception('Order item must have a valid positive price');
        }
        if (is_nan($price)) {
            throw new \Exception('Order item must not be not a number');
        }
    }

    public function total(): float
    {
        return $this->price * $this->quantity;
    }
}
