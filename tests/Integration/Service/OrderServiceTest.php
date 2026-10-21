<?php

declare(strict_types=1);

namespace Integration\Service;

use DifferDev\Application\Service\OrderService;
use DifferDev\Domain\Entity\DiscountCalculator;
use DifferDev\Domain\Entity\Order;
use DifferDev\Domain\Entity\OrderItem;
use DifferDev\Domain\Repository\OrderRepositoryInterface;
use DifferDev\Infrastructure\Repository\InMemory\InMemoryOrderRepository;
use Exception;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(OrderService::class)]
#[CoversClass(Order::class)]
#[CoversClass(OrderItem::class)]
#[CoversClass(DiscountCalculator::class)]
#[CoversClass(InMemoryOrderRepository::class)]
final class OrderServiceTest extends TestCase
{
    protected OrderRepositoryInterface $orderRepository;

    protected function setUp(): void
    {
        $this->orderRepository = new InMemoryOrderRepository();
    }

    /**
     * @throws Exception
     */
    public function testShouldPersistCreatedOrder(): void
    {
        $service = new OrderService($this->orderRepository);

        $order1 = $service->create([
            new OrderItem(price: 100, quantity: 2)
        ]);

        $order2 = $service->create([
            new OrderItem(price: 200, quantity: 2)
        ]);

        $orderFound = $this->orderRepository->findById('1');

        $this->assertEquals(2, $order2->id);

        $this->assertSame(
            '1',
            $orderFound->id
        );
        $this->assertCount(1, $orderFound->items());
        $this->assertInstanceOf(OrderItem::class, $orderFound->items()[0]);
        $this->assertSame(100.0, $orderFound->items()[0]->price);
        $this->assertSame(2, $orderFound->items()[0]->quantity);
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
        $service = new OrderService($this->orderRepository);

        $order = $service->create($items);

        $this->assertSame($expectedTotal, $order->total());
    }

    /**
     * TODO: Arrumar esses testes de erro(colocar o OrderItem)
     * @return iterable<string, array{array<mixed>}>
     */
    public static function invalidItems(): iterable
    {
        yield 'empty order' => [[]];
        yield 'invalid total' => [[['price' => 0, 'quantity' => 1]]];
        yield 'missing price' => [[['quantity' => 1]]];
        yield 'missing quantity' => [[['price' => 100]]];
        yield 'non-numeric price' => [[['price' => '100', 'quantity' => 1]]];
        yield 'non-integer quantity' => [[['price' => 100, 'quantity' => 1.5]]];
    }

    #[DataProvider('invalidItems')]
    public function testShouldRejectInvalidItems(array $items): void
    {
        $this->expectException(Exception::class);

        $service = new OrderService($this->orderRepository);

        $service->create($items);
    }

    /**
     * @throws Exception
     */
    public function testShouldTotalBeZero()
    {
        $this->expectExceptionMessage('Order must have a total');

        $service = new OrderService($this->orderRepository);

        $order = $service->create([
            new OrderItem(price: 0, quantity: 1)
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
