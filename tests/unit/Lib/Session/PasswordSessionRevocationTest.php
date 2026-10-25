<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\SessionInvalidException;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\GameSessionInitializer;
use Stu\Module\PlayerSetting\Action\ChangePassword\ChangePassword;
use Stu\Module\PlayerSetting\Action\ChangePassword\ChangePasswordRequestInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserLastAction;
use Stu\Orm\Entity\UserRegistration;
use Stu\Orm\Repository\UserIpTableRepositoryInterface;
use Stu\Orm\Repository\UserLastActionRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class PasswordSessionRevocationTest extends StuTestCase
{
    public function testPasswordChangeRotatesCurrentSessionAndRejectsOtherSessionsOnDirectRoutes(): void
    {
        session_start();
        $user = $this->createUser();
        $_SESSION = ['uid' => 101, 'login' => 1];
        SessionCredential::bind($user);
        $previousSession = $_SESSION;
        $previousId = session_id();
        $this->changePassword($user);
        self::assertNotSame($previousId, session_id());
        self::assertNotSame($previousSession['credential_fingerprint'], $_SESSION['credential_fingerprint']);
        self::assertTrue(SessionCredential::isValid($user));
        $this->assertDirectRoute($user, ModuleEnum::OPTIONS, true);
        $currentSession = $_SESSION;
        foreach ([ModuleEnum::GAME, ModuleEnum::OPTIONS] as $module) {
            $_SESSION = $previousSession;
            $this->assertDirectRoute($user, $module, false);
        }
        $_SESSION = $currentSession;
        $this->assertDirectRoute($user, ModuleEnum::GAME, true);
        session_destroy();
    }

    public function testRecoveryHashChangeRejectsEveryPreviouslyAuthenticatedSession(): void
    {
        $user = $this->createUser();
        $_SESSION = ['uid' => 101, 'login' => 1];
        SessionCredential::bind($user);
        $oldSession = $_SESSION;
        $user->getRegistration()->setPassword(password_hash('Recovered123', PASSWORD_BCRYPT, ['cost' => 4]));
        foreach ([ModuleEnum::GAME, ModuleEnum::OPTIONS] as $module) {
            $_SESSION = $oldSession;
            $this->assertDirectRoute($user, $module, false);
        }
    }

    public static function invalidFingerprints(): array
    {
        return [[null], [''], [false], [123], ['foreign-session']];
    }

    #[DataProvider('invalidFingerprints')]
    public function testMissingAndMalformedFingerprintsFailClosed(mixed $fingerprint): void
    {
        $user = $this->createUser();
        $_SESSION = ['uid' => 101, 'login' => 1, 'credential_fingerprint' => $fingerprint];
        $this->assertDirectRoute($user, ModuleEnum::GAME, false);
    }

    public function testPasswordChangeDuringImpersonationDoesNotReplaceAdminCredential(): void
    {
        $user = $this->createUser();
        $_SESSION = ['uid' => 1, 'login' => 1, 'credential_fingerprint' => 'admin-fingerprint', 'impersonated_user_id' => 101];
        $this->changePassword($user);
        self::assertSame('admin-fingerprint', $_SESSION['credential_fingerprint']);
        self::assertSame(1, $_SESSION['uid']);
    }

    private function createUser(): User
    {
        $user = $this->mock(User::class);
        $registration = new UserRegistration($user)->setPassword(password_hash('Original123', PASSWORD_BCRYPT, ['cost' => 4]));
        $user->shouldReceive('getId')->andReturn(101);
        $user->shouldReceive('getLastAction')->andReturn(new UserLastAction($user));
        $user->shouldReceive('getRegistration')->andReturn($registration);
        return $user;
    }

    private function changePassword(User $user): void
    {
        $request = $this->mock(ChangePasswordRequestInterface::class);
        $request->shouldReceive('getCurrentPassword')->andReturn('Original123');
        $request->shouldReceive('getNewPassword')->andReturn('Changed123');
        $request->shouldReceive('getNewPasswordReEntered')->andReturn('Changed123');
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('save')->with($user)->once();
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('getUser')->andReturn($user);
        $context->shouldReceive('getInfo->addInformation')->once();
        new ChangePassword($request, $users, new PasswordHasher(4))->handle($context);
        self::assertTrue(password_verify('Changed123', $user->getRegistration()->getPassword()));
    }

    private function assertDirectRoute(User $user, ModuleEnum $module, bool $allowed): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_id('direct-session-test');
        }
        $sessionId = session_id();
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('find')->with(101)->once()->andReturn($user);
        $ips = $this->mock(UserIpTableRepositoryInterface::class);
        $lastActions = $this->mock(UserLastActionRepositoryInterface::class);
        $destruction = $this->mock(SessionDestructionInterface::class);
        $impersonation = $this->mock(AdminImpersonationInterface::class);
        if ($allowed) {
            $lastAction = $user->getLastAction();
            $lastActions->shouldReceive('save')->with($lastAction)->once();
            $ips->shouldReceive('findBySessionId')->with($sessionId)->once()->andReturnNull();
            $impersonation->shouldReceive('resolveUser')->with($user, false)->once()->andReturn($user);
            $impersonation->shouldReceive('isActive')->once()->andReturnFalse();
            $destruction->shouldNotReceive('destroySession');
        } else {
            $lastActions->shouldNotReceive('save');
            $ips->shouldNotReceive('findBySessionId');
            $impersonation->shouldNotReceive('resolveUser');
            $destruction->shouldReceive('destroySession')->once();
        }
        $session = new Session($ips, $users, $lastActions, $destruction, $impersonation);
        $login = $this->mock(SessionLoginInterface::class);
        $login->shouldNotReceive('checkLoginCookie');
        $initializer = new GameSessionInitializer($session, $login);
        if ($allowed) {
            self::assertSame($user, $initializer->initialize($module));
        } else {
            try {
                $initializer->initialize($module);
                self::fail('Invalidierte Sitzung wurde akzeptiert');
            } catch (SessionInvalidException) {
                self::assertNull($session->getUser());
            }
        }
    }
}
