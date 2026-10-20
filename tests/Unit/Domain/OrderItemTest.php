<?php

declare(strict_types=1);

namespace Unit\Domain;

use DifferDev\Domain\Entity\OrderItem;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(OrderItem::class)]
final class OrderItemTest extends TestCase
{
    public function testShouldCalculateItemTotalAndAllowPersistenceToAssignAnId(): void
    {
        $item = new OrderItem(25.50, 2);

        $this->assertNull($item->id);
        $this->assertSame(51.0, $item->total());

        $item->id = 'item-123';

        $this->assertSame('item-123', $item->id);
    }

    /** @return iterable<string, array{float}> */
    public static function invalidPrices(): iterable
    {
        yield 'negative' => [-0.01];
        yield 'infinite' => [INF];
        yield 'not a number' => [NAN];
    }

    #[DataProvider('invalidPrices')]
    public function testShouldRejectInvalidPrice(float $price): void
    {
        $this->expectException(Exception::class);

        new OrderItem($price, 1);
    }

    /** @return iterable<string, array{int}> */
    public static function invalidQuantities(): iterable
    {
        yield 'zero' => [0];
        yield 'negative' => [-1];
    }

    #[DataProvider('invalidQuantities')]
    public function testShouldRejectInvalidQuantity(int $quantity): void
    {
        $this->expectException(Exception::class);

        new OrderItem(10.0, $quantity);
    }
}
