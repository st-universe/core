<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class StorageQualityTest extends TestCase
{
    public function testMixedStockConsumesStandardThenLowestQuality(): void
    {
        $storage = new Storage()->setAmount(10)->addModuleQualityAmounts([1 => 3, 17 => 5]);

        self::assertSame([0 => 2, 1 => 3, 17 => 5], $storage->getModuleQualityAmounts());
        self::assertSame([0 => 2, 1 => 3], $storage->removeAmount(5));
        self::assertSame(5, $storage->getAmount());
        self::assertSame([17 => 5], $storage->getModuleQualityAmounts());
    }

    public function testSelectedQualityIsPreservedWhenMovingAndMerging(): void
    {
        $source = new Storage()->setAmount(10)->addModuleQualityAmounts([1 => 3, 17 => 5]);
        $target = new Storage()->setAmount(3)->addModuleQualityAmounts([17 => 1]);

        $packet = $source->removeAmount(3, 17);
        $target->setAmount($target->getAmount() + array_sum($packet))->addModuleQualityAmounts($packet);

        self::assertSame([17 => 3], $packet);
        self::assertSame([0 => 2, 1 => 3, 17 => 2], $source->getModuleQualityAmounts());
        self::assertSame([0 => 2, 17 => 4], $target->getModuleQualityAmounts());
        self::assertSame(13, $source->getAmount() + $target->getAmount());
    }

    public function testUnavailableQualityDoesNotMutateStock(): void
    {
        $storage = new Storage()->setAmount(10)->addModuleQualityAmounts([17 => 2]);
        try {
            $storage->removeAmount(3, 17);
            self::fail('Unavailable quality must be rejected');
        } catch (InvalidArgumentException) {
            self::assertSame(10, $storage->getAmount());
            self::assertSame([0 => 8, 17 => 2], $storage->getModuleQualityAmounts());
        }
    }

    public function testGenericReductionCannotLeaveMoreVariantsThanTotal(): void
    {
        $storage = new Storage()->setAmount(10)->addModuleQualityAmounts([1 => 3, 17 => 5]);
        $storage->setAmount(2);
        self::assertSame([17 => 2], $storage->getModuleQualityAmounts());
        $storage->setAmount(5);
        self::assertSame([0 => 3, 17 => 2], $storage->getModuleQualityAmounts());
    }
}
