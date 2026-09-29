<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Stu\IntegrationTestCase;
use Stu\TestSession;

class DealsRepositoryTest extends IntegrationTestCase
{
    public function testUnclaimedAuctionRemainsVisibleToWinnerAfterThirtyDays(): void
    {
        $dic = $this->getContainer();
        $entityManager = $dic->get(EntityManagerInterface::class);
        $repository = $dic->get(DealsRepositoryInterface::class);
        $commodity = $dic->get(CommodityRepositoryInterface::class)->find(1);
        $winnerId = TestSession::DEFAULT_USER_ID;
        $otherUserId = 102;
        $now = time();

        self::assertNotNull($commodity);

        $oldAuction = $repository->prototype()
            ->setAuction(true)
            ->setShip(false)
            ->setGiveCommodity($commodity)
            ->setWantedCommodity($commodity)
            ->setStart($now - 32 * 86400)
            ->setEnd($now - 31 * 86400)
            ->setAuctionUser($winnerId);

        $recentClaimedAuction = $repository->prototype()
            ->setAuction(true)
            ->setShip(false)
            ->setGiveCommodity($commodity)
            ->setWantedCommodity($commodity)
            ->setStart($now - 2 * 86400)
            ->setEnd($now - 86400)
            ->setAuctionUser($winnerId)
            ->setTakenTime($now - 3600);

        $repository->save($oldAuction);
        $repository->save($recentClaimedAuction);
        $entityManager->flush();

        try {
            self::assertTrue($repository->hasOwnAuctionsToTake($winnerId));
            self::assertContains($oldAuction, $repository->getOwnEndedAuctionsGoods($winnerId));
            self::assertFalse($repository->hasOwnAuctionsToTake($otherUserId));
            self::assertNotContains($oldAuction, $repository->getEndedAuctionsGoods($otherUserId));
            self::assertContains($recentClaimedAuction, $repository->getEndedAuctionsGoods($otherUserId));

            $oldAuction->setTakenTime($now);
            $repository->save($oldAuction);
            $entityManager->flush();

            self::assertFalse($repository->hasOwnAuctionsToTake($winnerId));
            self::assertNotContains($oldAuction, $repository->getOwnEndedAuctionsGoods($winnerId));
            self::assertNotContains($oldAuction, $repository->getEndedAuctionsGoods($winnerId));
            self::assertContains($recentClaimedAuction, $repository->getEndedAuctionsGoods($winnerId));
        } finally {
            $repository->delete($oldAuction);
            $repository->delete($recentClaimedAuction);
        }
    }
}
