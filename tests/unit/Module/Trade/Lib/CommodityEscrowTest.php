<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Stu\Orm\Entity\AuctionBid;
use Stu\Orm\Entity\OrionAuctionBid;

final class CommodityEscrowTest extends TestCase
{
    public function testEscrowMergesRepeatedBidsWithoutChangingGrades(): void
    {
        self::assertSame([0 => 2, 4 => 9, 70 => 3], CommodityEscrow::merge([0 => 2, 4 => 5], [4 => 4, 70 => 3]));
    }

    public function testPartialRefundKeepsAllModuleVariants(): void
    {
        [$refund, $payment] = CommodityEscrow::split([0 => 2, 4 => 9, 70 => 3], 7);
        self::assertSame([0 => 2, 4 => 5], $refund);
        self::assertSame([4 => 4, 70 => 3], $payment);
        [$account, $overflow] = CommodityEscrow::split($refund, 3);
        self::assertSame([0 => 2, 4 => 1], $account);
        self::assertSame([4 => 4], $overflow);
        self::assertSame([0 => 2, 4 => 9, 70 => 3], CommodityEscrow::merge(CommodityEscrow::merge($account, $overflow), $payment));
    }

    public function testLegacyBidRefundsStandardModulesAndNewBidStoresGrades(): void
    {
        foreach ([new AuctionBid(), new OrionAuctionBid()] as $bid) {
            $bid->setMaxAmount(10);
            self::assertSame([0 => 10], $bid->getModuleQualityAmounts());
            $bid->setModuleQualityAmounts([0 => 3, 17 => 7]);
            self::assertSame([0 => 3, 17 => 7], $bid->getModuleQualityAmounts());
        }
    }

    public function testInvalidEscrowAmountIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new AuctionBid()->setMaxAmount(5)->setModuleQualityAmounts([17 => 6]);
    }
}
