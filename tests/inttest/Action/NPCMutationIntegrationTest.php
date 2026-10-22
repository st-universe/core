<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\ActionTestCase;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Quest\QuestUserModeEnum;
use Stu\Lib\Session\SessionStringFactory;
use Stu\Module\Control\AccessCheckInterface;
use Stu\Module\Control\Component\Action\ActionContextFactoryInterface;
use Stu\Module\Control\Component\CallbackExecution;
use Stu\Module\Control\Component\ControllerDiscoveryInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\GameData;
use Stu\Module\Control\GameModuleAccessCheckerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\EmailNotificationSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\NPC\View\ShowPlayerDetails\ShowPlayerDetails;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\NPCLog;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\NPCQuestLog;
use Stu\Orm\Entity\NPCQuestUser;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class NPCMutationIntegrationTest extends ActionTestCase
{
    private ?string $previousMethod;
    private array $previousGet;
    private array $previousPost;
    private int $questId;
    private int $applicantId;

    #[\Override]
    public function setUp(): void
    {
        $this->previousMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $this->previousGet = $_GET;
        $this->previousPost = $_POST;
        parent::setUp();
        self::$testSession->setUserById(10);
        request::setMockVars(null);
        $_GET = [];
        $_POST = [];
        $dic = $this->getContainer();
        self::assertTrue($dic->get(GameModuleAccessCheckerInterface::class)->isAllowed(ModuleEnum::NPC));
        $notification = $this->mock(EmailNotificationSenderInterface::class);
        $notification->shouldReceive('sendNotification')->zeroOrMoreTimes();
        $dic->setAdditionalService(EmailNotificationSenderInterface::class, $notification);
        $em = $dic->get(EntityManagerInterface::class);
        $users = $dic->get(UserRepositoryInterface::class);
        $quest = new NPCQuest()->setUser($users->find(10))->setUserId(10)->setTitle('Testquest')->setText('Testbeschreibung')->setStart(time())->setTime(time());
        $em->persist(new PrivateMessageFolder()->setUser($users->find(103))->setSpecial(PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM));
        $em->persist($quest);
        $em->flush();
        $this->questId = $quest->getId();
        $applicant = new NPCQuestUser()->setQuest($quest)->setQuestId($this->questId)->setUser($users->find(101))->setUserId(101)->setMode(QuestUserModeEnum::APPLICANT);
        $em->persist($applicant);
        $em->flush();
        $this->applicantId = $applicant->getId();
    }

    #[\Override]
    public function tearDown(): void
    {
        $_GET = $this->previousGet;
        $_POST = $this->previousPost;
        request::setMockVars(null);
        if ($this->previousMethod === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $this->previousMethod;
        }
        parent::tearDown();
    }

    public static function actions(): iterable
    {
        foreach (['AcceptQuestApplication', 'RejectQuestApplication', 'InviteQuestUsers', 'ExcludeQuestUsers', 'AddQuestLogEntry', 'EndNPCQuest', 'LogPlayerDetails'] as $name) {
            yield $name => [$name];
        }
    }

    #[DataProvider('actions')]
    public function testForgedRequestsCannotMutateAndAuthorizedPostWorks(string $name): void
    {
        $before = $this->state();
        foreach ([['GET', 'missing'], ['GET', 'own'], ['POST', 'missing'], ['POST', 'wrong'], ['POST', 'foreign'], ['POST', 'consumed']] as [$method, $kind]) {
            $parameters = $this->parameters();
            if ($kind !== 'missing') {
                $parameters['sstr'] = $kind === 'wrong' ? 'invalid-token' : $this->token($kind === 'foreign' ? 11 : 10);
                if ($kind === 'consumed') {
                    self::assertTrue($this->getContainer()->get(SessionStringRepositoryInterface::class)->isValid($parameters['sstr'], 10));
                }
            }
            $this->dispatch($name, $method, $parameters);
            self::assertSame($before, $this->state(), $method . ' ' . $kind);
        }
        $parameters = $this->parameters() + ['sstr' => $this->token(10)];
        $this->dispatch($name, 'POST', $parameters);
        $after = $this->state();
        self::assertNotSame($before, $after);
        match ($name) {
            'AcceptQuestApplication' => self::assertSame(QuestUserModeEnum::ACTIVE_MEMBER->value, $after['users'][$this->applicantId]),
            'RejectQuestApplication', 'ExcludeQuestUsers' => self::assertSame(QuestUserModeEnum::REJECTED_EXCLUDED->value, $after['users'][$this->applicantId]),
            'InviteQuestUsers' => self::assertContains(QuestUserModeEnum::INVITED->value, $after['users']),
            'EndNPCQuest' => self::assertNotNull($after['end']),
            'AddQuestLogEntry' => self::assertContains('Neuer Testeintrag', $after['questLogs']),
            'LogPlayerDetails' => self::assertCount(count($before['auditLogs']) + 1, $after['auditLogs'])
        };
        $this->dispatch($name, 'POST', $parameters);
        self::assertSame($after, $this->state());
    }

    #[DataProvider('actions')]
    public function testOwnTokenDoesNotAuthorizeAnotherNPCsQuest(string $name): void
    {
        if ($name === 'LogPlayerDetails') {
            self::assertTrue(true);
            return;
        }
        $before = $this->state();
        self::$testSession->setUserById(11);
        $this->dispatch($name, 'POST', $this->parameters() + ['sstr' => $this->token(11)]);
        self::assertSame($before, $this->state());
    }

    public function testPlayerDetailsGetViewCannotCreateAnAuditEntry(): void
    {
        $before = $this->state();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['userid' => 101, 'reason' => 'Fremd ausgelöster Audittext'];
        $context = $this->mock(ViewControllerContext::class);
        $context->shouldReceive('setTemplateFile')->once();
        $context->shouldReceive('setTemplateVar')->with('USER_DETAILS', \Mockery::any())->once();
        $this->getContainer()->get(ShowPlayerDetails::class)->handle($context);
        self::assertSame($before, $this->state());
    }

    private function parameters(): array
    {
        return ['quest_id' => $this->questId, 'quest_user_id' => $this->applicantId, 'user_ids' => '101,103', 'log_text' => 'Neuer Testeintrag', 'userid' => 101, 'reason' => 'Berechtigte Testeinsicht'];
    }

    private function token(int $userId): string
    {
        $dic = $this->getContainer();
        return new SessionStringFactory($dic->get(SessionStringRepositoryInterface::class), new StuTime())->createSessionString($dic->get(UserRepositoryInterface::class)->find($userId));
    }

    private function dispatch(string $name, string $method, array $parameters): void
    {
        $class = 'Stu\\Module\\NPC\\Action\\' . $name . ($name === 'LogPlayerDetails' ? '' : '\\' . $name);
        $_SERVER['REQUEST_METHOD'] = $method;
        $parameters[$class::ACTION_IDENTIFIER] = '1';
        $_GET = $method === 'GET' ? $parameters : [];
        $_POST = $method === 'POST' ? $parameters : [];
        $dic = $this->getContainer();
        $action = $dic->get($class);
        $discovery = $this->mock(ControllerDiscoveryInterface::class);
        $discovery->shouldReceive('getControllers')->with(ModuleEnum::NPC, false)->once()->andReturn([$class::ACTION_IDENTIFIER => $action]);
        $data = new GameData();
        $request = new GameRequest();
        $game = $this->mock(GameControllerInterface::class);
        $game->shouldReceive('getGameData')->andReturn($data);
        $game->shouldReceive('getInfo')->andReturn($data->gameInformations);
        $game->shouldReceive('getGameRequest')->andReturn($request);
        $game->shouldReceive('getUser')->andReturn(self::$testSession->getUser());
        new CallbackExecution($discovery, $dic->get(AccessCheckInterface::class), $dic->get(ActionContextFactoryInterface::class), new StuTime(), $dic->get(EntityManagerInterface::class))->execute(ModuleEnum::NPC, $game);
        self::assertSame($class::ACTION_IDENTIFIER, $request->getAction());
    }

    private function state(): array
    {
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $quest = $em->find(NPCQuest::class, $this->questId);
        $em->refresh($quest);
        $users = [];
        foreach ($em->getRepository(NPCQuestUser::class)->findAll() as $entity) {
            $em->refresh($entity);
            $users[$entity->getId()] = $entity->getMode()->value;
        }
        return [
            'end' => $quest->getEnd(),
            'users' => $users,
            'questLogs' => array_map(static fn (NPCQuestLog $log): string => $log->getText(), $em->getRepository(NPCQuestLog::class)->findAll()),
            'auditLogs' => array_map(static fn (NPCLog $log): string => $log->getText(), $em->getRepository(NPCLog::class)->findAll()),
            'messages' => $em->getRepository(PrivateMessage::class)->count([])
        ];
    }
}
