<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use request;
use Stu\ActionTestCase;
use Stu\Component\Quest\QuestUserModeEnum;
use Stu\Module\Control\Component\Action\ActionContextFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\GameData;
use Stu\Module\Message\Lib\EmailNotificationSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Orm\Entity\AuctionBid;
use Stu\Orm\Entity\Deals;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\NPCQuestUser;
use Stu\Orm\Entity\PrestigeLog;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TradeOffer;
use Stu\Orm\Entity\TradeTransaction;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\NPCQuestUserRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;

final class EconomyClaimIntegrationTest extends ActionTestCase
{
    private ?string $previousMethod;

    #[\Override]
    public function setUp(): void
    {
        $this->previousMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        parent::setUp();
        request::setMockVars(null);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = [];
        $_POST = [];
        self::$testSession->setUserById(101);
        $notification = $this->mock(EmailNotificationSenderInterface::class);
        $notification->shouldReceive('sendNotification')->zeroOrMoreTimes();
        $this->getContainer()->setAdditionalService(EmailNotificationSenderInterface::class, $notification);
    }

    #[\Override]
    public function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        if ($this->previousMethod === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $this->previousMethod;
        }
        parent::tearDown();
    }

    public function testQuestRewardIsGrantedOnceAndCanonicalDuplicateParticipationCannotClaimAgain(): void
    {
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $user = $em->find(User::class, 101);
        $quest = new NPCQuest()->setUser($user)->setUserId(101)->setTitle('Einmalige Testbelohnung')->setEnd(time())->setPrestige(7)->setCommodityReward([2 => 5]);
        $em->persist($quest);
        $em->flush();
        foreach ([1, 2] as $_) {
            $em->persist(new NPCQuestUser()->setQuest($quest)->setQuestId($quest->getId())->setUser($user)->setUserId(101)->setMode(QuestUserModeEnum::ACTIVE_MEMBER));
        }
        $em->flush();
        $commodity = $em->find(Storage::class, 2);
        $stored = $commodity->getAmount();
        $before = $user->getPrestige();
        $logs = $em->getRepository(PrestigeLog::class)->count([]);
        $this->dispatch('Communication', 'ClaimQuestReward', ['questid' => $quest->getId(), 'colonyid' => 42]);
        $em->refresh($user);
        $em->refresh($commodity);
        self::assertSame($stored + 5, $commodity->getAmount());
        self::assertSame($before + 7, $user->getPrestige());
        self::assertSame($logs + 1, $em->getRepository(PrestigeLog::class)->count([]));
        $this->dispatch('Communication', 'ClaimQuestReward', ['questid' => $quest->getId(), 'colonyid' => 42]);
        $em->refresh($user);
        $em->refresh($commodity);
        self::assertSame($stored + 5, $commodity->getAmount());
        self::assertSame($before + 7, $user->getPrestige());
        self::assertSame($logs + 1, $em->getRepository(PrestigeLog::class)->count([]));
        self::$testSession->setUserById(102);
        $this->dispatch('Communication', 'ClaimQuestReward', ['questid' => $quest->getId(), 'colonyid' => 42]);
        self::assertSame($logs + 1, $em->getRepository(PrestigeLog::class)->count([]));
    }

    public function testAuctionBuildplanAndOverbidRefundAreGrantedOnceToWinner(): void
    {
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $user = $em->find(User::class, 101);
        $plan = $em->getRepository(SpacecraftBuildplan::class)->findOneBy([]);
        $auction = new Deals()->setAuction(true)->setAuctionUser(101)->setAuctionAmount(10)->setStart(time() - 200)->setEnd(time() - 100)->setBuildplan($plan)->setShip(false);
        $em->persist($auction);
        $em->flush();
        $bid = new AuctionBid()->setAuction($auction)->setAuctionId($auction->getId())->setUser($user)->setUserId(101)->setMaxAmount(15);
        $auction->getAuctionBids()->add($bid);
        $em->persist($bid);
        $em->flush();
        $plans = $em->getRepository(SpacecraftBuildplan::class)->count([]);
        $prestige = $user->getPrestige();
        self::$testSession->setUserById(102);
        $this->dispatch('Trade', 'DealsTakeAuction', ['dealid' => $auction->getId()]);
        self::assertSame($plans, $em->getRepository(SpacecraftBuildplan::class)->count([]));
        self::$testSession->setUserById(101);
        $this->dispatch('Trade', 'DealsTakeAuction', ['dealid' => $auction->getId()]);
        $em->refresh($auction);
        $em->refresh($user);
        self::assertNotNull($auction->getTakenTime());
        self::assertSame($plans + 1, $em->getRepository(SpacecraftBuildplan::class)->count([]));
        self::assertSame($prestige + 5, $user->getPrestige());
        $this->dispatch('Trade', 'DealsTakeAuction', ['dealid' => $auction->getId()]);
        $em->refresh($user);
        self::assertSame($plans + 1, $em->getRepository(SpacecraftBuildplan::class)->count([]));
        self::assertSame($prestige + 5, $user->getPrestige());
    }

    public function testFinalOfferUnitCannotBeBoughtTwice(): void
    {
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $offer = $em->find(TradeOffer::class, 1);
        $offer->setOfferCount(1);
        $offer->getStorage()->setAmount($offer->getOfferedCommodityCount());
        $buyer = $em->find(User::class, 102);
        $payment = $em->getRepository(Storage::class)->findOneBy(['user_id' => 102, 'tradepost_id' => $offer->getTradePostId(), 'commodity_id' => $offer->getWantedCommodityId()])
            ?? new Storage()->setUser($buyer)->setCommodity($offer->getWantedCommodity())->setTradePost($offer->getTradePost());
        $payment->setAmount(20);
        $em->persist($payment);
        $folder = new PrivateMessageFolder()->setUser($em->find(User::class, 101))->setSpecial(PrivateMessageFolderTypeEnum::SPECIAL_TRADE);
        $em->persist($folder);
        $em->flush();
        $em->refresh($payment);
        self::$testSession->setUserById(102);
        $transactions = $em->getRepository(TradeTransaction::class)->count([]);
        $this->dispatch('Trade', 'TakeOffer', ['offerid' => 1, 'amount' => 5]);
        self::assertNull($this->getContainer()->get(TradeOfferRepositoryInterface::class)->find(1));
        self::assertSame($transactions + 1, $em->getRepository(TradeTransaction::class)->count([]));
        $this->dispatch('Trade', 'TakeOffer', ['offerid' => 1, 'amount' => 1]);
        self::assertSame($transactions + 1, $em->getRepository(TradeTransaction::class)->count([]));
        $em->refresh($payment);
        self::assertSame(18, $payment->getAmount());
    }

    public function testLockedReadsRefreshAlreadyManagedState(): void
    {
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $offer = $em->find(TradeOffer::class, 1);
        self::assertSame(14, $offer->getOfferCount());
        $em->createQuery(sprintf('UPDATE %s entry SET entry.amount = 3 WHERE entry.id = 1', TradeOffer::class))->execute();
        self::assertSame(14, $offer->getOfferCount());
        $em->wrapInTransaction(function () use ($offer): void {
            $locked = $this->getContainer()->get(TradeOfferRepositoryInterface::class)->getForUpdate(1);
            self::assertSame($offer, $locked);
            self::assertSame(3, $locked->getOfferCount());
        });
        $em->wrapInTransaction(function (): void {
            self::assertNull($this->getContainer()->get(DealsRepositoryInterface::class)->getForUpdate(PHP_INT_MAX));
            self::assertNull($this->getContainer()->get(NPCQuestUserRepositoryInterface::class)->getByQuestAndUserForUpdate(PHP_INT_MAX, 101));
        });
    }

    private function dispatch(string $module, string $name, array $parameters): void
    {
        $dic = $this->getContainer();
        $_GET = $parameters;
        $class = 'Stu\\Module\\' . $module . '\\Action\\' . $name . '\\' . $name;
        $arguments = [];
        foreach (new ReflectionClass($class)->getConstructor()->getParameters() as $parameter) {
            $type = $parameter->getType()->getName();
            $arguments[] = str_ends_with($type, 'RequestInterface') ? new (substr($type, 0, -strlen('Interface')))() : $dic->get($type);
        }
        $action = new $class(...$arguments);
        $game = $this->mock(GameControllerInterface::class);
        $data = new GameData();
        $game->shouldReceive('getGameData')->andReturn($data);
        $game->shouldReceive('getInfo')->andReturn($data->gameInformations);
        $game->shouldReceive('getUser')->andReturn(self::$testSession->getUser());
        $game->shouldReceive('getGameRequest')->andReturn(new GameRequest());
        $context = $dic->get(ActionContextFactoryInterface::class)->createActionContext($game);
        $dic->get(EntityManagerInterface::class)->wrapInTransaction(static fn () => $action->handle($context));
    }
}
