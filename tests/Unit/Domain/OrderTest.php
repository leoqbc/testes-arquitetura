<?php

declare(strict_types=1);

namespace Unit\Domain;

use DifferDev\Domain\Entity\DiscountCalculator;
use DifferDev\Domain\Entity\Order;
use DifferDev\Domain\Entity\OrderItem;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Order::class)]
#[CoversClass(OrderItem::class)]
#[CoversClass(DiscountCalculator::class)]
final class OrderTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testShouldCalculateTheTotalFromItsItems(): void
    {
        $order = new Order([
            new OrderItem(100.0, 2),
            new OrderItem(25.0, 3),
        ]);

        $this->assertSame(275.0, $order->total());
    }

    /**
     * @throws Exception
     */
    public function testShouldExposeItemsWithoutAllowingTheCollectionToBeChanged(): void
    {
        $item = new OrderItem(100.0, 1);
        $order = new Order([2 => $item]);

        $items = $order->items();
        array_pop($items);

        $this->assertSame([$item], $order->items());
    }

    public function testShouldRejectAnEmptyOrder(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Order must have items');

        new Order([]);
    }

    public function testShouldRejectValuesThatAreNotOrderItems(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Order item '0' must be an instance of OrderItem"
        );

        new Order([['price' => 100, 'quantity' => 1]]);
    }

    /**
     * @throws Exception
     */
    public function testShouldApplyDiscountToTheOrderTotal(): void
    {
        $order = new Order([new OrderItem(100.0, 2)]);

        $order->discount = 0.10;

        $this->assertSame(180.0, $order->total());
    }

    /**
     * @throws Exception
     */
    public function testShouldReplaceAPreviouslyAppliedDiscount(): void
    {
        $order = new Order([new OrderItem(100.0, 2)]);

        $order->discount = 0.10;

        $this->assertSame(180.0, $order->total());
    }
}
