<?php

namespace DifferDev\Infrastructure\Repository\InMemory;

use DifferDev\Domain\Entity\Order;
use DifferDev\Domain\Repository\OrderRepositoryInterface;

class InMemoryOrderRepository implements OrderRepositoryInterface
{
    protected array $orders = [];

    public function save(Order $order): void
    {
        $id = $order->id;
        if (null === $id) {
            $id = count($this->orders) + 1;
            $order->id = $id;
        }
        $this->orders[$id] = $order;
    }

    public function findById(string $id): Order
    {
        $order = $this->orders[$id] ?? null;

        if (null === $order) {
            throw new \Exception('Order not found');
        }

        return $order;
    }
}