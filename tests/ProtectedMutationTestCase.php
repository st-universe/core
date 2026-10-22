<?php

declare(strict_types=1);

namespace Stu;

use request;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Session\SessionInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\AccessCheck;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\GameUserRoleCheckerInterface;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SessionStringRepositoryInterface;

abstract class ProtectedMutationTestCase extends StuTestCase
{
    private ?string $previousMethod;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->previousMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    #[\Override]
    protected function tearDown(): void
    {
        if ($this->previousMethod === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $this->previousMethod;
        }
        parent::tearDown();
    }

    protected function assertRequestProtection(ActionControllerInterface $action): void
    {
        self::assertTrue($action->performSessionCheck());
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $action->handle($this->mock(ActionControllerContext::class));
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(101);
        $user->shouldReceive('getState')->andReturn(UserStateEnum::ACTIVE);
        $session = $this->mock(SessionInterface::class);
        $session->shouldReceive('getUser')->andReturn($user);
        $tokens = $this->mock(SessionStringRepositoryInterface::class);
        $tokens->shouldReceive('isValid')->with('foreign', 101)->once()->andReturn(false);
        $tokens->shouldReceive('isValid')->with('consumed', 101)->once()->andReturn(false);
        $tokens->shouldReceive('isValid')->with('own', 101)->once()->andReturn(true);
        $access = new AccessCheck($tokens, $this->mock(StuConfigInterface::class), $this->mock(GameUserRoleCheckerInterface::class), $session);
        $info = $this->mock(InformationInterface::class);
        foreach (['', 'foreign', 'consumed'] as $token) {
            request::setMockVars(['sstr' => $token]);
            self::assertFalse($access->checkUserAccess($action, $info));
        }
        request::setMockVars(['sstr' => 'own']);
        self::assertTrue($access->checkUserAccess($action, $info));
    }
}
