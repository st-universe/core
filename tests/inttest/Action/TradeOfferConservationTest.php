<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\ActionTestCase;
use Stu\Exception\AccessViolationException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Session\SessionStringFactory;
use Stu\Module\Control\AccessCheckInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionContextFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Trade\Action\CancelOffer\CancelOffer;
use Stu\Module\Trade\Action\CreateOffer\CreateOffer;
use Stu\Module\Trade\Action\TakeOffer\TakeOffer;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TradeOffer;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class TradeOfferConservationTest extends ActionTestCase
{
    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        self::$testSession->setUserById(101);
        $storage = $this->getContainer()->get(StorageRepositoryInterface::class)->find(13);
        $storage->setAmount(100);
        $this->getContainer()->get(StorageRepositoryInterface::class)->save($storage);
        $this->getContainer()->get(EntityManagerInterface::class)->flush();
    }

    #[DataProvider('mismatchedBalances')]
    public function testClientCommodityCannotChangeOfferedGoods(int $balance): void
    {
        if ($balance > 0) {
            $this->addBalance(101, 21, $balance);
        }
        $before = $this->totals();
        $parameters = $this->parameters(10, 1);
        $parameters['ggid'] = 21;
        $this->executeAction($this->getContainer()->get(CreateOffer::class), $parameters);
        self::assertSame([], $this->offers(21));
        $offers = $this->offers(8);
        self::assertCount(1, $offers);
        self::assertSame(8, $offers[0]->getStorage()->getCommodityId());
        self::assertSame(10, $offers[0]->getStorage()->getAmount());
        self::assertSame(90, $this->balance(101, 8));
        self::assertSame($before, $this->totals());
        $this->cancel($offers[0]);
        self::assertSame($before, $this->totals());
        self::assertSame(100, $this->balance(101, 8));
        self::assertSame($balance, $this->balance(101, 21));
    }

    public static function mismatchedBalances(): iterable
    {
        yield 'missing' => [0];
        yield 'insufficient' => [3];
        yield 'sufficient but mismatched' => [100];
    }

    public function testCommodityDoesNotNeedToBeSubmitted(): void
    {
        $twig = new Environment(new FilesystemLoader(__DIR__ . '/../../../src'), ['strict_variables' => true]);
        $html = $twig->render('html/trade/tradeOfferMenu.twig', [
            'STOR' => $this->getContainer()->get(StorageRepositoryInterface::class)->find(13),
            'IS_NPC_POST' => false,
            'SELECTABLE_COMMODITIES' => []
        ]);
        self::assertStringNotContainsString('name="ggid"', $html);
        self::assertStringContainsString('name="storid" value="13"', $html);
        $before = $this->totals();
        $parameters = $this->parameters(10, 1);
        $this->executeAction($this->getContainer()->get(CreateOffer::class), $parameters);
        $offers = $this->offers(8);
        self::assertCount(1, $offers);
        self::assertSame(8, $offers[0]->getStorage()->getCommodityId());
        self::assertSame($before, $this->totals());
        $this->cancel($offers[0]);
        self::assertSame($before, $this->totals());
    }

    #[DataProvider('invalidSources')]
    public function testSourceValidationRemainsEffective(string $restriction): void
    {
        $dic = $this->getContainer();
        $parameters = $this->parameters(10, 1);
        $parameters['ggid'] = 21;
        $before = $this->totals();
        $offerCount = count($dic->get(TradeOfferRepositoryInterface::class)->findAll());
        if ($restriction === 'missing') {
            $parameters['storid'] = 999999;
        } elseif ($restriction === 'untradeable') {
            $dic->get(CommodityRepositoryInterface::class)->find(8)->setView(false);
            $dic->get(EntityManagerInterface::class)->flush();
        } else {
            $parameters['wgid'] = 8;
        }
        $this->executeAction($dic->get(CreateOffer::class), $parameters);
        self::assertCount($offerCount, $dic->get(TradeOfferRepositoryInterface::class)->findAll());
        self::assertSame(100, $this->balance(101, 8));
        self::assertSame($before, $this->totals());
    }

    public static function invalidSources(): iterable
    {
        yield ['missing'];
        yield ['untradeable'];
        yield ['same wanted commodity'];
    }

    #[DataProvider('legitimateOffers')]
    public function testCreateAndCancelConserveEachCommodity(int $balance, int $give, int $requested, int $expectedCount): void
    {
        $dic = $this->getContainer();
        $storage = $dic->get(StorageRepositoryInterface::class)->find(13);
        $storage->setAmount($balance);
        $dic->get(StorageRepositoryInterface::class)->save($storage);
        $dic->get(EntityManagerInterface::class)->flush();
        $before = $this->totals();

        $this->executeAction($dic->get(CreateOffer::class), $this->parameters($give, $requested));
        $offers = $this->offers(8);
        self::assertCount($expectedCount > 0 ? 1 : 0, $offers);
        self::assertSame($before, $this->totals());
        self::assertSame($balance - $give * $expectedCount, $this->balance(101, 8));
        if ($expectedCount > 0) {
            $offer = $offers[0];
            self::assertSame($expectedCount, $offer->getOfferCount());
            self::assertSame($give * $expectedCount, $offer->getStorage()->getAmount());
            self::assertSame(8, $offer->getStorage()->getCommodityId());
            $this->cancel($offer);
            self::assertSame($before, $this->totals());
            self::assertSame($balance, $this->balance(101, 8));
            self::assertSame([], $this->offers(8));
        }
    }

    public static function legitimateOffers(): iterable
    {
        yield 'partial balance' => [100, 10, 3, 3];
        yield 'entire balance' => [100, 100, 1, 1];
        yield 'reduced offer count' => [100, 30, 5, 3];
        yield 'not enough for one offer' => [5, 10, 2, 0];
        yield 'empty balance' => [0, 10, 1, 0];
        yield 'existing count limit' => [100, 10, 100, 1];
        yield 'zero quantity' => [100, 0, 1, 0];
        yield 'negative quantity' => [100, -10, 1, 0];
    }

    public function testNormalPurchaseAndRemainingCancellationConserveGoods(): void
    {
        $dic = $this->getContainer();
        $this->addBalance(102, 21, 10);
        $licenses = $dic->get(TradeLicenseRepositoryInterface::class);
        $licenses->save($licenses->prototype()
            ->setUser($dic->get(UserRepositoryInterface::class)->find(102))
            ->setTradePost($dic->get(TradePostRepositoryInterface::class)->find(2))
            ->setDate(time())
            ->setExpired(time() + 3600));
        $dic->get(EntityManagerInterface::class)->flush();
        $before = $this->totals();
        $parameters = $this->parameters(10, 2);
        $parameters['wgid'] = 21;
        $this->executeAction($dic->get(CreateOffer::class), $parameters);
        $offer = $this->offers(8, 21)[0];
        self::$testSession->setUserById(102);
        $this->executeAction($dic->get(TakeOffer::class), ['offerid' => $offer->getId(), 'amount' => 1]);
        self::assertSame($before, $this->totals());
        self::assertSame(10, $this->balance(102, 8));
        self::assertSame(9, $this->balance(102, 21));
        self::assertSame(1, $this->balance(101, 21));
        self::$testSession->setUserById(101);
        $this->cancel($this->offers(8, 21)[0]);
        self::assertSame($before, $this->totals());
        self::assertSame(90, $this->balance(101, 8));
    }

    public function testForeignStorageCannotFundOffer(): void
    {
        $dic = $this->getContainer();
        $storage = $this->addBalance(102, 8, 100);
        $parameters = $this->parameters(10, 1);
        $parameters['storid'] = $storage->getId();
        try {
            $this->executeAction($dic->get(CreateOffer::class), $parameters);
            self::fail('Ein fremdes Warenkonto darf kein Angebot finanzieren');
        } catch (AccessViolationException) {
            self::assertSame([], $this->offers(8));
        }
    }

    private function parameters(int $give, int $amount): array
    {
        return ['storid' => 13, 'gcount' => $give, 'wgid' => 2, 'wcount' => 1, 'amount' => $amount];
    }

    private function executeAction(ActionControllerInterface $action, array $parameters): void
    {
        $dic = $this->getContainer();
        $token = new SessionStringFactory($dic->get(SessionStringRepositoryInterface::class), new StuTime())->createSessionString(self::$testSession->getUser());
        request::setMockVars($parameters + ['sstr' => $token]);
        $dic->get(EntityManagerInterface::class)->wrapInTransaction(function () use ($dic, $action): void {
            self::assertTrue($dic->get(AccessCheckInterface::class)->checkUserAccess($action, new InformationWrapper()));
            $action->handle($dic->get(ActionContextFactoryInterface::class)->createActionContext($dic->get(GameControllerInterface::class)));
        });
    }

    private function offers(int $commodity, int $wanted = 2): array
    {
        $dic = $this->getContainer();
        $offers = $dic->get(TradeOfferRepositoryInterface::class)->getByTradePostAndUserAndCommodities(2, 101, $commodity, $wanted);
        foreach ($offers as $offer) {
            $dic->get(EntityManagerInterface::class)->refresh($offer);
        }
        return $offers;
    }

    private function cancel(TradeOffer $offer): void
    {
        $this->executeAction($this->getContainer()->get(CancelOffer::class), ['offerid' => $offer->getId()]);
    }

    private function addBalance(int $userId, int $commodityId, int $amount): Storage
    {
        $dic = $this->getContainer();
        $storage = $dic->get(StorageRepositoryInterface::class)->prototype()
            ->setUser($dic->get(UserRepositoryInterface::class)->find($userId))
            ->setTradePost($dic->get(TradePostRepositoryInterface::class)->find(2))
            ->setCommodity($dic->get(CommodityRepositoryInterface::class)->find($commodityId))
            ->setAmount($amount);
        $dic->get(StorageRepositoryInterface::class)->save($storage);
        $dic->get(EntityManagerInterface::class)->flush();
        $dic->get(EntityManagerInterface::class)->refresh($storage);
        return $storage;
    }

    private function balance(int $userId, int $commodityId): int
    {
        $dic = $this->getContainer();
        $storage = $dic->get(StorageRepositoryInterface::class)->getByTradepostAndUserAndCommodity(2, $userId, $commodityId);
        if ($storage === null) {
            return 0;
        }
        $dic->get(EntityManagerInterface::class)->refresh($storage);
        return $storage->getAmount();
    }

    private function totals(): array
    {
        $dic = $this->getContainer();
        $result = [];
        foreach ([8, 21] as $commodityId) {
            $result[$commodityId] = 0;
            foreach ($dic->get(StorageRepositoryInterface::class)->findBy(['commodity_id' => $commodityId]) as $storage) {
                $dic->get(EntityManagerInterface::class)->refresh($storage);
                $result[$commodityId] += $storage->getAmount();
            }
        }
        return $result;
    }
}
