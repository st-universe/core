<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\TakeOffer;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use request;
use Stu\ActionControllerTestCase;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\NPC\Lib\NpcLogTradeMessageLoggerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\Lib\TradePostStorageManagerInterface;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TradeOffer;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\TradeTransaction;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradeTransactionRepositoryInterface;

final class TakeOfferQualityTest extends ActionControllerTestCase
{
    /** @return iterable<string, array{int, int, array<int, int>, array<int, int>}> */
    public static function trades(): iterable
    {
        yield 'partial mixed-quality offer' => [1, 1, [0 => 2, 17 => 1], [17 => 3, 70 => 3]];
        yield 'entire offer caps requested amount' => [99, 3, [0 => 2, 17 => 4, 70 => 3], []];
    }

    /** @param array<int, int> $received
     * @param array<int, int> $remaining
     */
    #[DataProvider('trades')]
    public function testTradePreservesOfferedAndSelectedPaymentQuality(int $requested, int $taken, array $received, array $remaining): void
    {
        $buyer = new User();
        $seller = new User();
        $post = new TradePost()->setName('Handelsposten');
        $offered = new Commodity()->setName('Impulsantrieb');
        $wanted = new Commodity()->setName('Warpantrieb');
        $this->assign($buyer, 'id', 501);
        $this->assign($seller, 'id', 502);
        $this->assign($post, 'id', 7);
        $post->setUser($seller);
        $this->assign($offered, 'id', 10401);
        $this->assign($wanted, 'id', 10901);
        $offerStorage = new Storage()->setAmount(9);
        $offerStorage->addModuleQualityAmounts([17 => 4, 70 => 3]);
        $paymentStorage = new Storage()->setAmount(60)->setTradePost($post);
        $paymentStorage->addModuleQualityAmounts([8 => 30, 70 => 30]);
        $offer = new TradeOffer()->setTradePost($post)->setTradePostId(7)->setUser($seller)
            ->setOfferCount(3)->setOfferedCommodity($offered)->setOfferedCommodityId(10401)->setOfferedCommodityCount(3)
            ->setWantedCommodity($wanted)->setWantedCommodityId(10901)->setWantedCommodityCount(2);
        $this->assign($offer, 'user_id', 502);
        $this->assign($offer, 'storage', $offerStorage);
        request::setMockVars(['quality' => [10901 => 8]]);
        $request = $this->mock(TakeOfferRequestInterface::class);
        $request->shouldReceive('getOfferId')->once()->andReturn(42);
        $request->shouldReceive('getAmount')->once()->andReturn($requested);
        $offers = $this->mock(TradeOfferRepositoryInterface::class);
        $offers->shouldReceive('getForUpdate')->with(42)->once()->andReturn($offer);
        $licenses = $this->mock(TradeLicenseRepositoryInterface::class);
        $licenses->shouldReceive('hasLicenseByUserAndTradePost')->with(501, 7)->once()->andReturnTrue();
        $storages = $this->mock(StorageRepositoryInterface::class);
        $storages->shouldReceive('getByTradepostAndUserAndCommodity')->with(7, 501, 10901)->once()->andReturn($paymentStorage);
        if ($taken === 3) {
            $offers->shouldReceive('delete')->with($offer)->once();
            $storages->shouldReceive('delete')->with($offerStorage)->once();
        } else {
            $offers->shouldReceive('save')->with($offer)->once();
            $storages->shouldReceive('save')->with($offerStorage)->once();
        }
        $factory = $this->mock(TradeLibFactoryInterface::class);
        $buyerStorage = $this->mock(TradePostStorageManagerInterface::class);
        $sellerStorage = $this->mock(TradePostStorageManagerInterface::class);
        $factory->shouldReceive('createTradePostStorageManager')->with($post, $buyer)->once()->andReturn($buyerStorage);
        $factory->shouldReceive('createTradePostStorageManager')->with($post, $seller)->once()->andReturn($sellerStorage);
        $buyerStorage->shouldReceive('getFreeStorage')->once()->andReturn(100);
        $buyerStorage->shouldReceive('removeStorage')->with(10901, $taken * 2, 8)->once()->andReturn([8 => $taken * 2]);
        $sellerStorage->shouldReceive('addStorage')->with(10901, [8 => $taken * 2])->once();
        $buyerStorage->shouldReceive('addStorage')->with(10401, $received)->once();
        $transactions = $this->mock(TradeTransactionRepositoryInterface::class);
        $transactions->shouldReceive('prototype')->once()->andReturn(new TradeTransaction());
        $transactions->shouldReceive('save')->once();
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $messages->shouldReceive('send')->once();
        $logger = $this->mock(NpcLogTradeMessageLoggerInterface::class);
        $logger->shouldReceive('logIfNpcInvolved')->once();
        $this->game->shouldReceive('getUser')->andReturn($buyer);
        $this->game->shouldReceive('getInfo')->andReturn(new InformationWrapper());
        $this->game->shouldReceive('setView')->once();
        $this->game->shouldReceive('setViewContext')->once();
        new TakeOffer($request, $factory, $offers, $licenses, $messages, $transactions, $storages, $logger)->handle($this->game);
        self::assertSame($remaining, $offerStorage->getModuleQualityAmounts());
        self::assertSame(9 - 3 * $taken, $offerStorage->getAmount());
    }

    private function assign(object $entity, string $field, mixed $value): void
    {
        new ReflectionProperty($entity, $field)->setValue($entity, $value);
    }
}
