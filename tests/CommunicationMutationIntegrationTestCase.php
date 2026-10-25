<?php

declare(strict_types=1);

namespace Stu;

use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use request;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Exception\AccessViolationException;
use Stu\Lib\Session\SessionStringFactory;
use Stu\Module\Control\AccessCheckInterface;
use Stu\Module\Control\Component\Action\ActionContextFactoryInterface;
use Stu\Module\Control\Component\CallbackExecution;
use Stu\Module\Control\Component\ControllerDiscoveryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\GameData;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\EmailNotificationSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Orm\Entity\AllianceBoard;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\AllianceJobPermission;
use Stu\Orm\Entity\AllianceMemberJob;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\KnComment;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Entity\RpgPlotMember;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Repository\AllianceMemberJobRepositoryInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

abstract class CommunicationMutationIntegrationTestCase extends ActionTestCase
{
    protected array $ids = [];
    private ?string $previousMethod;
    private array $previousGet;
    private array $previousPost;

    #[\Override]
    public function setUp(): void
    {
        $this->previousMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $this->previousGet = $_GET;
        $this->previousPost = $_POST;
        parent::setUp();
        self::$testSession->setUserById(101);
        request::setMockVars(null);
        $_GET = [];
        $_POST = [];
        $dic = $this->getContainer();
        $notification = $this->mock(EmailNotificationSenderInterface::class);
        $notification->shouldReceive('sendNotification')->zeroOrMoreTimes();
        $dic->setAdditionalService(EmailNotificationSenderInterface::class, $notification);
        $em = $dic->get(EntityManagerInterface::class);
        $users = $dic->get(UserRepositoryInterface::class);
        $owner = $users->find(101);
        $foreign = $users->find(102);
        $this->ids = [];
        foreach (['origin' => $owner, 'destination' => $owner, 'foreignFolder' => $foreign] as $key => $user) {
            $folder = new PrivateMessageFolder()->setUser($user)->setDescription($key)->setSort(8);
            $em->persist($folder);
            $this->ids[$key] = $folder;
        }
        foreach (['message' => $owner, 'foreignMessage' => $foreign] as $key => $user) {
            $message = new PrivateMessage()->setSender($foreign)->setRecipient($user)->setCategory($this->ids[$key === 'message' ? 'origin' : 'foreignFolder'])->setText($key)->setDate(time());
            $em->persist($message);
            $this->ids[$key] = $message;
        }
        foreach (['plot' => $owner, 'foreignPlot' => $foreign] as $key => $user) {
            $plot = new RpgPlot()->setUser($user)->setTitle($key)->setDescription('Originalbeschreibung')->setStartDate(time());
            $em->persist($plot);
            $this->ids[$key] = $plot;
        }
        $post = new KnPost()->setUser($owner)->setTitle('Testbeitrag')->setText('Testtext')->setUsername('Testautor')->setDate(time());
        $em->persist($post);
        $this->ids['post'] = $post;
        foreach (['comment' => $owner, 'foreignComment' => $foreign] as $key => $user) {
            $comment = new KnComment()->setUser($user)->setPosting($post)->setText($key)->setDate(time());
            $em->persist($comment);
            $this->ids[$key] = $comment;
        }
        $rump = $em->getRepository(SpacecraftRump::class)->findOneBy([]);
        foreach (['plan' => $owner, 'foreignPlan' => $foreign] as $key => $user) {
            $plan = new SpacecraftBuildplan()->setUser($user)->setRump($rump)->setName($key)->setBuildtime(1)->setCrew(0);
            $em->persist($plan);
            $this->ids[$key] = $plan;
        }
        $dic->get(AllianceMemberJobRepositoryInterface::class)->truncateByUser(101);
        $job = new AllianceJob()->setAlliance($owner->getAlliance())->setTitle('Testverwalter');
        $permission = new AllianceJobPermission()->setJob($job)->setPermission(AllianceJobPermissionEnum::EDIT_ALLIANCE->value);
        $job->getPermissions()->add($permission);
        $em->persist($job);
        $em->persist($permission);
        $em->persist(new AllianceMemberJob()->setUser($owner)->setJob($job));
        $em->persist(new PrivateMessageFolder()->setUser($users->find(103))->setSpecial(PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM));
        $em->flush();
        foreach ($this->ids as $key => $entity) {
            $this->ids[$key] = $entity->getId();
            $em->refresh($entity);
        }
    }

    #[\Override]
    public function tearDown(): void
    {
        $_GET = $this->previousGet;
        $_POST = $this->previousPost;
        if ($this->previousMethod === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $this->previousMethod;
        }
        request::setMockVars(null);
        parent::tearDown();
    }

    abstract public static function actions(): iterable;

    protected function assertForgedRequestsLeaveDatabaseUnchangedAndAuthorizedPostWorks(string $class): void
    {
        $parameters = $this->parameters($class);
        $before = $this->state();
        foreach ([['GET', 'missing'], ['GET', 'own'], ['POST', 'missing'], ['POST', 'wrong'], ['POST', 'foreign'], ['POST', 'consumed']] as [$method, $kind]) {
            $request = $parameters;
            if ($kind !== 'missing') {
                $request['sstr'] = $kind === 'wrong' ? 'ungueltiger-token' : $this->token($kind === 'foreign' ? 102 : 101);
                if ($kind === 'consumed') {
                    self::assertTrue($this->getContainer()->get(SessionStringRepositoryInterface::class)->isValid($request['sstr'], 101));
                }
            }
            $this->dispatch($class, $method, $request);
            self::assertSame($before, $this->state(), $method . ' ' . $kind);
        }
        $parameters['sstr'] = $this->token(101);
        $this->dispatch($class, 'POST', $parameters);
        $after = $this->state();
        self::assertNotSame($before, $after);
        $this->assertEffect($class, $before, $after);
        $this->dispatch($class, 'POST', $parameters);
        self::assertSame($after, $this->state());
    }

    protected function assertOwnTokenCannotChangeForeignObjects(): void
    {
        $before = $this->state();
        foreach (static::actions() as [$class]) {
            $name = new ReflectionClass($class)->getShortName();
            $parameters = match ($name) {
                'DeletePms' => ['deletion_mark' => (string) $this->ids['foreignMessage']],
                'DeletePmCategory' => ['pmcat' => $this->ids['foreignFolder']],
                'MovePm' => ['move_pm' => $this->ids['foreignMessage'], 'movecat_' . $this->ids['foreignMessage'] => $this->ids['destination']],
                'EditPmCategory' => ['pmcat' => $this->ids['foreignFolder'], 'catname' => 'Fremder Name'],
                'SortPmCategories' => ['catlist' => (string) $this->ids['foreignFolder']],
                'DeleteBuildPlan' => ['planid' => $this->ids['foreignPlan']],
                'EditKnPlot' => ['plotid' => $this->ids['foreignPlot'], 'title' => 'Fremder Titel', 'description' => 'Fremder Text'],
                'ReopenKnPlot' => ['plotid' => $this->ids['foreignPlot']],
                'DeleteKnComment' => ['commentid' => $this->ids['foreignComment'], 'knid' => $this->ids['post']],
                'AddKnPlotMember' => ['plotid' => $this->ids['foreignPlot'], 'memid' => 103],
                default => null
            };
            if ($parameters === null) {
                continue;
            }
            try {
                $this->dispatch($class, 'POST', $parameters + ['sstr' => $this->token(101)]);
            } catch (AccessViolationException) {
                self::assertContains($name, ['EditPmCategory', 'SortPmCategories']);
            }
            self::assertSame($before, $this->state(), $name);
        }
    }

    protected function parameters(string $class): array
    {
        return match (new ReflectionClass($class)->getShortName()) {
            'DeletePms' => ['deletion_mark' => (string) $this->ids['message']],
            'DeletePmCategory' => ['pmcat' => $this->ids['origin']],
            'MovePm' => ['move_pm' => $this->ids['message'], 'movecat_' . $this->ids['message'] => $this->ids['destination']],
            'EditPmCategory' => ['pmcat' => $this->ids['origin'], 'catname' => 'Neuer Ordnername'],
            'SortPmCategories' => ['catlist' => $this->ids['destination'] . ',' . $this->ids['origin']],
            'DeleteBuildPlan' => ['planid' => $this->ids['plan']],
            'AddBoard' => ['board' => 'Neues Testforum'],
            'EditKnPlot' => ['plotid' => $this->ids['plot'], 'title' => 'Neuer Plottitel', 'description' => 'Neue Beschreibung'],
            'ReopenKnPlot' => ['plotid' => $this->ids['plot']],
            'CreateKnPlot' => ['title' => 'Neuer Testplot', 'description' => 'Neue Beschreibung'],
            'PostKnComment' => ['knid' => $this->ids['post'], 'comment' => 'Neuer Testkommentar'],
            'DeleteKnComment' => ['knid' => $this->ids['post'], 'commentid' => $this->ids['comment']],
            'AddKnPlotMember' => ['plotid' => $this->ids['plot'], 'memid' => 103]
        };
    }

    protected function token(int $userId): string
    {
        $dic = $this->getContainer();
        return new SessionStringFactory($dic->get(SessionStringRepositoryInterface::class), new StuTime())->createSessionString($dic->get(UserRepositoryInterface::class)->find($userId));
    }

    protected function dispatch(string $class, string $method, array $parameters): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $parameters[$class::ACTION_IDENTIFIER] = '1';
        $_GET = $method === 'GET' ? $parameters : [];
        $_POST = $method === 'POST' ? $parameters : [];
        $dic = $this->getContainer();
        $arguments = [];
        foreach (new ReflectionClass($class)->getConstructor()->getParameters() as $parameter) {
            $type = $parameter->getType()->getName();
            $arguments[] = str_ends_with($type, 'RequestInterface') ? new (substr($type, 0, -strlen('Interface')))() : $dic->get($type);
        }
        $action = new $class(...$arguments);
        $module = $this->getModuleOfController($action, []);
        $discovery = $this->mock(ControllerDiscoveryInterface::class);
        $discovery->shouldReceive('getControllers')->with($module, false)->once()->andReturn([$class::ACTION_IDENTIFIER => $action]);
        $game = $this->mock(GameControllerInterface::class);
        $data = new GameData();
        $gameRequest = new GameRequest();
        $game->shouldReceive('getGameData')->andReturn($data);
        $game->shouldReceive('getInfo')->andReturn($data->gameInformations);
        $game->shouldReceive('getGameRequest')->andReturn($gameRequest);
        $game->shouldReceive('getUser')->andReturn(self::$testSession->getUser());
        new CallbackExecution($discovery, $dic->get(AccessCheckInterface::class), $dic->get(ActionContextFactoryInterface::class), new StuTime(), $dic->get(EntityManagerInterface::class))->execute($module, $game);
        self::assertSame($class::ACTION_IDENTIFIER, $gameRequest->getAction());
    }

    protected function state(): array
    {
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $state = [];
        foreach ([PrivateMessage::class, PrivateMessageFolder::class, RpgPlot::class, KnComment::class, SpacecraftBuildplan::class, AllianceBoard::class, RpgPlotMember::class] as $class) {
            $state[$class] = [];
            foreach ($em->getRepository($class)->findAll() as $entity) {
                $em->refresh($entity);
                $state[$class][$entity->getId()] = match ($class) {
                    PrivateMessage::class => [$entity->getRecipientId(), $entity->getCategoryId(), $entity->isDeleted()],
                    PrivateMessageFolder::class => [$entity->getUserId(), $entity->getDescription(), $entity->getSort(), $entity->isDeleted()],
                    RpgPlot::class => [$entity->getUserId(), $entity->getTitle(), $entity->getDescription(), $entity->getEndDate(), $entity->getEditDate()],
                    KnComment::class => [$entity->getUserId(), $entity->getText(), $entity->isDeleted()],
                    SpacecraftBuildplan::class => [$entity->getUserId(), $entity->getName()],
                    AllianceBoard::class => [$entity->getAlliance()->getId(), $entity->getName()],
                    RpgPlotMember::class => [$entity->getUserId(), $entity->getPlotId()]
                };
            }
            ksort($state[$class]);
        }
        return $state;
    }

    private function assertEffect(string $class, array $before, array $after): void
    {
        match (new ReflectionClass($class)->getShortName()) {
            'DeletePms' => self::assertTrue($after[PrivateMessage::class][$this->ids['message']][2]),
            'DeletePmCategory' => self::assertSame([true, true], [$after[PrivateMessageFolder::class][$this->ids['origin']][3], $after[PrivateMessage::class][$this->ids['message']][2]]),
            'MovePm' => self::assertSame($this->ids['destination'], $after[PrivateMessage::class][$this->ids['message']][1]),
            'EditPmCategory' => self::assertSame('Neuer Ordnername', $after[PrivateMessageFolder::class][$this->ids['origin']][1]),
            'SortPmCategories' => self::assertSame([0, 1], [$after[PrivateMessageFolder::class][$this->ids['destination']][2], $after[PrivateMessageFolder::class][$this->ids['origin']][2]]),
            'DeleteBuildPlan' => self::assertArrayNotHasKey($this->ids['plan'], $after[SpacecraftBuildplan::class]),
            'AddBoard' => self::assertCount(count($before[AllianceBoard::class]) + 1, $after[AllianceBoard::class]),
            'EditKnPlot' => self::assertSame('Neuer Plottitel', $after[RpgPlot::class][$this->ids['plot']][1]),
            'ReopenKnPlot' => self::assertNull($after[RpgPlot::class][$this->ids['plot']][3]),
            'CreateKnPlot' => self::assertCount(count($before[RpgPlot::class]) + 1, $after[RpgPlot::class]),
            'PostKnComment' => self::assertCount(count($before[KnComment::class]) + 1, $after[KnComment::class]),
            'DeleteKnComment' => self::assertTrue($after[KnComment::class][$this->ids['comment']][2]),
            'AddKnPlotMember' => self::assertCount(count($before[RpgPlotMember::class]) + 1, $after[RpgPlotMember::class])
        };
    }
}
