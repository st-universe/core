<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Stu\ActionTestCase;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\OrionAuctionBidRepositoryInterface;
use Stu\Orm\Repository\OrionAuctionRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class CrewDeletionIntegrationTest extends ActionTestCase
{
    public function testDeletingAuctionedCrewRemovesAuctionAndBidsBeforeFlush(): void
    {
        $container = $this->getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $crews = $container->get(CrewRepositoryInterface::class);
        $auctions = $container->get(OrionAuctionRepositoryInterface::class);
        $bids = $container->get(OrionAuctionBidRepositoryInterface::class);
        $user = $container->get(UserRepositoryInterface::class)->find(101);
        $race = $container->get(CrewRaceRepositoryInterface::class)->find(3);
        self::assertNotNull($user);
        self::assertNotNull($race);
        $crew = $crews->prototype()->setUser($user)
            ->setRace($race);
        $crews->save($crew);
        $auction = $auctions->prototype()->setCrew($crew)->setStart(1)->setEnd(2)
            ->setCompletedAt(2)->setWinner($user)->setAuctionAmount(100);
        $auctions->save($auction);
        $bid = $bids->prototype()->setAuction($auction)->setUser($user)->setMaxAmount(100);
        $bids->save($bid);
        $em->flush();
        $crewId = $crew->getId();
        $auctionId = $auction->getId();
        $bidId = $bid->getId();

        $crews->delete($crew);
        $em->flush();
        $em->clear();

        self::assertNull($crews->find($crewId));
        self::assertNull($auctions->find($auctionId));
        self::assertNull($bids->find($bidId));
        self::assertNotNull($crews->find(2));
    }
}
