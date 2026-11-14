<?php

declare(strict_types=1);

namespace Stu\Action;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use request;
use Stu\ActionTestCase;
use Stu\Component\Borg\BorgAssimilationActionGuard;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Game\RedirectionException;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Lib\Session\SessionInterface;
use Stu\Lib\Session\SessionLoginInterface;
use Stu\Module\Control\AccessCheck;
use Stu\Module\Control\Component\CallbackExecution;
use Stu\Module\Control\Component\ControllerDiscoveryInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\StuTime;
use Stu\Module\Index\Action\Login\Login;
use Stu\Module\Index\Action\Login\LoginRequestInterface;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;

final class BorgAssimilationLoginIntegrationTest extends ActionTestCase
{
    /** @return iterable<string, array{bool}> */
    public static function loginResults(): iterable
    {
        yield 'failed anonymous login reaches its handler' => [false];
        yield 'successful anonymous login establishes a session before redirecting' => [true];
    }

    #[DataProvider('loginResults')]
    public function testRealCallbackAndAssimilationGuardAllowAnAnonymousLogin(bool $successful): void
    {
        $container = $this->getContainer();
        $currentUser = null;
        $session = $this->mock(SessionInterface::class);
        $session->shouldReceive('getUser')->andReturnUsing(static function () use (&$currentUser): ?User {
            return $currentUser;
        });
        $discovery = $this->mock(ControllerDiscoveryInterface::class);
        $access = $container->make(AccessCheck::class, ['session' => $session]);
        $guard = $container->get(BorgAssimilationActionGuard::class);
        $callback = $container->make(CallbackExecution::class, [
            'controllerDiscovery' => $discovery,
            'accessCheck' => $access,
            'assimilationActionGuard' => $guard,
            'stuTime' => new StuTime()
        ]);
        $game = $container->make(GameController::class, ['session' => $session, 'callbackExecution' => $callback]);
        $gameRequest = new GameRequest();
        new ReflectionProperty(GameController::class, 'gameRequest')->setValue($game, $gameRequest);
        $user = $container->get(UserRepositoryInterface::class)->find(101);
        self::assertNotNull($user);
        $loginRequest = $this->mock(LoginRequestInterface::class);
        $loginRequest->shouldReceive('getLoginName')->once()->andReturn('anonymous-login-test');
        $loginRequest->shouldReceive('getPassword')->once()->andReturn('test-password');
        $sessionLogin = $this->mock(SessionLoginInterface::class);
        $sessionLogin->shouldReceive('login')->once()->with('anonymous-login-test', 'test-password')->andReturnUsing(
            static function () use ($successful, $user, $game, &$currentUser): bool {
                self::assertFalse($game->hasUser());
                if ($successful) {
                    $currentUser = $user;
                }
                return $successful;
            }
        );
        $login = $container->make(Login::class, ['loginRequest' => $loginRequest, 'sessionLogin' => $sessionLogin]);
        $discovery->shouldReceive('getControllers')->once()->with(ModuleEnum::INDEX, false)->andReturn([Login::ACTION_IDENTIFIER => $login]);
        request::setMockVars([Login::ACTION_IDENTIFIER => 1]);
        self::assertFalse($game->hasUser());

        try {
            $callback->execute(ModuleEnum::INDEX, $game);
            self::assertFalse($successful, 'Ein erfolgreicher Login muss regulär weiterleiten');
        } catch (RedirectionException $exception) {
            self::assertTrue($successful);
            $view = $container->get(UserSettingsProviderInterface::class)->getDefaultView($user);
            self::assertSame('/' . $view->getPhpPage(), $exception->getMessage());
        }

        self::assertSame($successful, $game->hasUser());
        self::assertSame(Login::ACTION_IDENTIFIER, $gameRequest->getAction());
        self::assertSame([], $gameRequest->getErrors());
    }
}
