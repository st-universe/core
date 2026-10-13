<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use request;
use Stu\Component\Game\RedirectionException;
use Stu\Lib\Session\AdminImpersonationInterface;
use Stu\Module\Admin\View\Playerlist\Playerlist;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

final class ImpersonationTest extends StuTestCase
{
    private ?string $requestMethod;

    protected function setUp(): void
    {
        $this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->requestMethod === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $this->requestMethod;
        }
        parent::tearDown();
    }

    public function testStartRequiresPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $access = $this->mock(AdminImpersonationInterface::class);
        $access->shouldNotReceive('start');
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldNotReceive('find');
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView')->with(Playerlist::VIEW_IDENTIFIER)->once();
        $action = new StartImpersonation($access, $users);

        self::assertTrue($action->performSessionCheck());
        $action->handle($context);
    }

    public function testStartLogsBothIdentitiesAndRedirectsIntoGame(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        request::setMockVars(['uid' => 101]);
        $admin = $this->mock(User::class);
        $admin->shouldReceive('getId')->andReturn(1);
        $player = $this->mock(User::class);
        $player->shouldReceive('getId')->andReturn(101);
        $access = $this->mock(AdminImpersonationInterface::class);
        $access->shouldReceive('start')->with($admin, $player)->once();
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('find')->with(101)->once()->andReturn($player);
        $request = new GameRequest();
        $request->setParameterArray(['admin_access' => ['admin_id' => 999]]);
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView')->with(Playerlist::VIEW_IDENTIFIER)->once();
        $context->shouldReceive('getUser')->once()->andReturn($admin);
        $context->shouldReceive('getGame->getGameRequest')->once()->andReturn($request);

        try {
            new StartImpersonation($access, $users)->handle($context);
            self::fail('Expected redirect');
        } catch (RedirectionException $e) {
            self::assertSame('/maindesk.php', $e->getHref());
        }
        self::assertSame(['admin_id' => 1, 'user_id' => 101], $request->getParameterArray()['admin_access']);
    }

    public function testStopRequiresPostAndSessionCheck(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $access = $this->mock(AdminImpersonationInterface::class);
        $access->shouldNotReceive('stop');
        $action = new StopImpersonation($access);

        self::assertTrue($action->performSessionCheck());
        $action->handle($this->mock(ActionControllerContext::class));
    }

    public function testStopRedirectsBackToPlayerList(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $access = $this->mock(AdminImpersonationInterface::class);
        $access->shouldReceive('stop')->once();

        try {
            new StopImpersonation($access)->handle($this->mock(ActionControllerContext::class));
            self::fail('Expected redirect');
        } catch (RedirectionException $e) {
            self::assertSame('/admin/?SHOW_PLAYER_LIST=1', $e->getHref());
        }
    }

    public function testAuditDataCannotBeSpoofedByNormalRequest(): void
    {
        $request = new GameRequest();
        $request->setParameterArray(['admin_access' => ['admin_id' => 1, 'user_id' => 101]]);

        $request->setAdminAccess(null, null);

        self::assertArrayNotHasKey('admin_access', $request->getParameterArray());
    }
}
