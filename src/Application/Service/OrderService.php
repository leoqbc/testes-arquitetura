<?php

declare(strict_types=1);

namespace DifferDev\Application\Service;

use DifferDev\Application\DTO\OrderDTO;
use DifferDev\Domain\Entity\Order;
use DifferDev\Domain\Entity\OrderItem;
use DifferDev\Domain\Repository\OrderRepositoryInterface;
use Exception;
use InvalidArgumentException;

final readonly class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * @param array $items
     * @return Order
     * @throws Exception
     */
    public function create(array $items): Order
    {
        $order = new Order($items);

        $order->canBeCreated();

        $this->orderRepository->save($order);

        return $order;
    }
}
