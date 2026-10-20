<?php

declare(strict_types=1);

namespace Integration\Service;

use DifferDev\Application\Service\OrderService;
use DifferDev\Domain\Entity\DiscountCalculator;
use DifferDev\Domain\Entity\Order;
use DifferDev\Domain\Entity\OrderItem;
use DifferDev\Domain\Repository\OrderRepositoryInterface;
use Exception;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(OrderService::class)]
#[CoversClass(Order::class)]
#[CoversClass(OrderItem::class)]
#[CoversClass(DiscountCalculator::class)]
final class OrderServiceTest extends TestCase
{
    public function testShouldPersistCreatedOrder(): void
    {
        $repository = Mockery::mock(OrderRepositoryInterface::class);
        $repository
            ->shouldReceive('save')
            ->andReturnUsing(function (Order $order): void {
                $order->id = '123';
            })
            ->once()
        ;

        $service = new OrderService($repository);

        $order = $service->create([
            new OrderItem(price: 100, quantity: 2)
        ]);

        $this->assertSame(
            '123',
            $order->id
        );
        $this->assertCount(1, $order->items());
        $this->assertInstanceOf(OrderItem::class, $order->items()[0]);
        $this->assertSame(100.0, $order->items()[0]->price);
        $this->assertSame(2, $order->items()[0]->quantity);
    }

    /**
     * @return iterable<string, array{array<int, OrderItem>}
     * @throws Exception
     */
    public static function orderTotals(): iterable
    {
        yield 'above discount threshold' => [
            [ new OrderItem(price: 300, quantity: 2) ],
            600.0,
        ];
        yield 'exactly at discount threshold' => [
            [ new OrderItem(price: 250, quantity: 2) ],
            500.0,
        ];
    }

    #[DataProvider('orderTotals')]
    public function testShouldApplyDiscountOnlyAboveTheThreshold(
        array $items,
        float $expectedTotal,
    ): void {
        $repository = Mockery::mock(OrderRepositoryInterface::class);
        $repository->shouldReceive('save')->once();

        $service = new OrderService($repository);

        $order = $service->create($items);

        $this->assertSame($expectedTotal, $order->total());
    }

    /** @return iterable<string, array{array<mixed>}> */
    public static function invalidItems(): iterable
    {
        yield 'empty order' => [[]];
        yield 'missing price' => [[['quantity' => 1]]];
        yield 'missing quantity' => [[['price' => 100]]];
        yield 'non-numeric price' => [[['price' => '100', 'quantity' => 1]]];
        yield 'non-integer quantity' => [[['price' => 100, 'quantity' => 1.5]]];
    }

    #[DataProvider('invalidItems')]
    public function testShouldRejectInvalidItems(array $items): void
    {
        $this->expectException(Exception::class);

        $repository = Mockery::mock(OrderRepositoryInterface::class);
        $repository->shouldNotReceive('save');

        $service = new OrderService($repository);

        $service->create($items);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
