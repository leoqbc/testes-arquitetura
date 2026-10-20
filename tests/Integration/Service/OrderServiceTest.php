<?php

namespace Integration\Service;

use DifferDev\Application\Service\OrderService;
use DifferDev\Domain\Entity\Order;
use DifferDev\Domain\Repository\OrderRepositoryInterface;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OrderService::class)]
#[CoversClass(Order::class)]
class OrderServiceTest extends TestCase
{
    public function testShouldPersistCreatedOrder(): void
    {
        $repository = Mockery::mock(OrderRepositoryInterface::class);
        $repository
            ->shouldReceive('save')
            ->andReturnUsing(function (Order $order): void {
                $order->id = '123';
            })
        ;

        $service = new OrderService($repository);

        $order = $service->create([
            ['price' => 100, 'quantity' => 2]
        ]);

        $this->assertSame(
            '123',
            $order->id
        );
    }
}
