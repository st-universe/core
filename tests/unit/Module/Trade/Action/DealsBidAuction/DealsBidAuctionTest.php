<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsBidAuction;

use Mockery\MockInterface;
use ReflectionProperty;
use Stu\ActionControllerTestCase;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowDeals\ShowDeals;
use Stu\Orm\Entity\AuctionBid;
use Stu\Orm\Entity\Deals;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AuctionBidRepositoryInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

class DealsBidAuctionTest extends ActionControllerTestCase
{
    private MockInterface&DealsBidAuctionRequestInterface $request;
    private MockInterface&TradeLibFactoryInterface $tradeLibFactory;
    private MockInterface&AuctionBidRepositoryInterface $auctionBidRepository;
    private MockInterface&DealsRepositoryInterface $dealsRepository;
    private MockInterface&TradePostRepositoryInterface $tradepostRepository;
    private MockInterface&TradeLicenseRepositoryInterface $tradeLicenseRepository;
    private MockInterface&StorageRepositoryInterface $storageRepository;
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;
    private MockInterface&CreatePrestigeLogInterface $createPrestigeLog;
    private MockInterface&StuTime $stuTime;

    private DealsBidAuction $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->request = $this->mock(DealsBidAuctionRequestInterface::class);
        $this->tradeLibFactory = $this->mock(TradeLibFactoryInterface::class);
        $this->auctionBidRepository = $this->mock(AuctionBidRepositoryInterface::class);
        $this->dealsRepository = $this->mock(DealsRepositoryInterface::class);
        $this->tradepostRepository = $this->mock(TradePostRepositoryInterface::class);
        $this->tradeLicenseRepository = $this->mock(TradeLicenseRepositoryInterface::class);
        $this->storageRepository = $this->mock(StorageRepositoryInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->createPrestigeLog = $this->mock(CreatePrestigeLogInterface::class);
        $this->stuTime = $this->mock(StuTime::class);

        $this->subject = new DealsBidAuction(
            $this->request,
            $this->tradeLibFactory,
            $this->auctionBidRepository,
            $this->dealsRepository,
            $this->tradepostRepository,
            $this->tradeLicenseRepository,
            $this->storageRepository,
            $this->privateMessageSender,
            $this->createPrestigeLog,
            $this->stuTime
        );
    }

    public function testHandleDoesNotAllowHighestBidderToLowerMaxBid(): void
    {
        $user = (new User())->setPrestige(1_000)->setUsername('Bidder');
        $this->setPrivateProperty($user, 'id', 42);

        $auction = (new Deals())
            ->setAuction(true)
            ->setAuctionAmount(651)
            ->setwantPrestige(1)
            ->setStart(1)
            ->setEnd(100);

        $highestBid = (new AuctionBid())
            ->setUser($user)
            ->setMaxAmount(1_000)
            ->setAuction($auction);
        $auction->getAuctionBids()->add($highestBid);

        $info = new InformationWrapper();

        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $this->game->shouldReceive('setView')
            ->with(ShowDeals::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($info);

        $this->request->shouldReceive('getDealId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);
        $this->request->shouldReceive('getMaxAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(652);

        $this->dealsRepository->shouldReceive('find')
            ->with(123)
            ->once()
            ->andReturn($auction);

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(50);

        $this->tradeLicenseRepository->shouldReceive('hasFergLicense')
            ->with(42)
            ->once()
            ->andReturn(true);

        $this->createPrestigeLog->shouldReceive('createLog')->never();
        $this->auctionBidRepository->shouldReceive('save')->never();
        $this->dealsRepository->shouldReceive('save')->never();

        $this->subject->handle($this->game);

        self::assertSame(1_000, $highestBid->getMaxAmount());
        self::assertSame(
            ['Dein neues Maximalgebot muss über deinem bisherigen Maximalgebot liegen'],
            $info->getInformations()
        );
    }

    private function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflectionProperty = new ReflectionProperty($object, $property);
        $reflectionProperty->setValue($object, $value);
    }
}
