<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsBidAuction;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\ActionControllerTestCase;
use Stu\Component\Trade\TradeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\Lib\TradePostStorageManagerInterface;
use Stu\Module\Trade\View\ShowDeals\ShowDeals;
use Stu\Orm\Entity\AuctionBid;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Deals;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TradePost;
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

    #[Override]
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
        $user = new User()->setPrestige(1_000)->setUsername('Bidder');
        $this->setPrivateProperty($user, 'id', 42);

        $auction = new Deals()
            ->setAuction(true)
            ->setAuctionAmount(651)
            ->setwantPrestige(1)
            ->setStart(1)
            ->setEnd(100);

        $highestBid = new AuctionBid()
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

    /** @return iterable<string, array{string}> */
    public static function commodityBids(): iterable
    {
        yield 'first bid' => ['first'];
        yield 'raise own bid' => ['own'];
        yield 'outbid and refund previous bidder' => ['other'];
    }

    #[DataProvider('commodityBids')]
    public function testModuleQualityIsReservedAndRefundedExactly(string $mode): void
    {
        $user = new User()->setUsername('Bidder');
        $other = new User()->setUsername('Previous bidder');
        $this->setPrivateProperty($user, 'id', 42);
        $this->setPrivateProperty($other, 'id', 77);
        $commodity = new Commodity()->setName('Impulsantrieb');
        $this->setPrivateProperty($commodity, 'id', 10401);
        $auction = new Deals()->setAuction(true)->setAuctionAmount($mode === 'first' ? 0 : 5)
            ->setWantedCommodity($commodity)->setStart(1)->setEnd(100);
        $old = new AuctionBid()->setUser($mode === 'own' ? $user : $other)->setUserId($mode === 'own' ? 42 : 77)
            ->setAuction($auction)->setMaxAmount(10)->setModuleQualityAmounts([4 => 4, 70 => 6]);
        if ($mode !== 'first') {
            $auction->getAuctionBids()->add($old);
        }
        $created = new AuctionBid();
        $post = new TradePost();
        $storage = $this->mock(TradePostStorageManagerInterface::class);
        $this->request->shouldReceive('getDealId')->once()->andReturn(123);
        $this->request->shouldReceive('getMaxAmount')->once()->andReturn(15);
        $this->dealsRepository->shouldReceive('find')->with(123)->once()->andReturn($auction);
        if ($mode !== 'own') {
            $this->dealsRepository->shouldReceive('save')->with($auction)->once();
        }
        $this->stuTime->shouldReceive('time')->once()->andReturn(50);
        $this->tradeLicenseRepository->shouldReceive('hasFergLicense')->with(42)->once()->andReturnTrue();
        $this->storageRepository->shouldReceive('getByTradepostAndUserAndCommodity')
            ->with(TradeEnum::DEALS_FERG_TRADEPOST_ID, 42, 10401)->once()->andReturn(new Storage()->setAmount(40));
        $this->tradepostRepository->shouldReceive('find')->with(TradeEnum::DEALS_FERG_TRADEPOST_ID)->once()->andReturn($post);
        $this->tradeLibFactory->shouldReceive('createTradePostStorageManager')->with($post, $user)->once()->andReturn($storage);
        $needed = $mode === 'own' ? 5 : 15;
        $storage->shouldReceive('removeStorage')->with(10401, $needed)->once()->andReturn([17 => $needed]);
        if ($mode === 'other') {
            $refundStorage = $this->mock(TradePostStorageManagerInterface::class);
            $this->tradeLibFactory->shouldReceive('createTradePostStorageManager')->with($post, $other)->once()->andReturn($refundStorage);
            $refundStorage->shouldReceive('addStorage')->with(10401, [4 => 4, 70 => 6])->once();
            $this->privateMessageSender->shouldReceive('send')->once();
        }
        if ($mode === 'own') {
            $this->auctionBidRepository->shouldReceive('save')->with($old)->once();
        } else {
            $this->auctionBidRepository->shouldReceive('prototype')->once()->andReturn($created);
            $this->auctionBidRepository->shouldReceive('save')->with($created)->once();
        }
        $this->game->shouldReceive('getUser')->andReturn($user);
        $this->game->shouldReceive('setView')->with(ShowDeals::VIEW_IDENTIFIER)->once();
        $this->game->shouldReceive('getInfo')->andReturn(new InformationWrapper());
        $this->subject->handle($this->game);
        $bid = $mode === 'own' ? $old : $created;
        self::assertSame(15, $bid->getMaxAmount());
        self::assertSame($mode === 'own' ? [4 => 4, 17 => 5, 70 => 6] : [17 => 15], $bid->getModuleQualityAmounts());
    }

    private function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflectionProperty = new ReflectionProperty($object, $property);
        $reflectionProperty->setValue($object, $value);
    }
}
