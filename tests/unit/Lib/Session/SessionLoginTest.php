<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\StuHashInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserRegistration;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserIpTableRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

final class SessionLoginTest extends StuTestCase
{
    public function testMasterPasswordDoesNotReplaceUsersOutdatedHash(): void
    {
        $user = new User();
        $hash = password_hash('player-password', PASSWORD_BCRYPT, ['cost' => 4]);
        $registration = new UserRegistration($user)->setPassword($hash);
        $user->setRegistration($registration);
        $config = $this->mock(StuConfigInterface::class);
        $config->shouldReceive('getSecuritySettings->getMasterPassword')->once()->andReturn('admin-password');
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldNotReceive('save');
        $subject = new SessionLogin(
            $this->mock(UserIpTableRepositoryInterface::class),
            $this->mock(SessionStringRepositoryInterface::class),
            $users,
            $this->mock(UserSettingsProviderInterface::class),
            $this->mock(SessionInterface::class),
            $this->mock(SessionDestructionInterface::class),
            $config,
            $this->mock(StuHashInterface::class),
            $this->mock(AdminImpersonationInterface::class)
        );

        $this->getMethod($subject, 'checkPassword')->invoke($subject, $user, 'admin-password');

        self::assertSame($hash, $registration->getPassword());
        self::assertTrue(password_verify('player-password', $registration->getPassword()));
    }

    public function testIndexRequestKeepsImpersonationWithoutCookieLoginSideEffects(): void
    {
        $session = $this->mock(SessionInterface::class);
        $session->shouldReceive('createSession')->once();
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldNotReceive('find', 'save');
        $impersonation = $this->mock(AdminImpersonationInterface::class);
        $impersonation->shouldReceive('isActive')->once()->andReturn(true);
        $subject = new SessionLogin(
            $this->mock(UserIpTableRepositoryInterface::class),
            $this->mock(SessionStringRepositoryInterface::class),
            $users,
            $this->mock(UserSettingsProviderInterface::class),
            $session,
            $this->mock(SessionDestructionInterface::class),
            $this->mock(StuConfigInterface::class),
            $this->mock(StuHashInterface::class),
            $impersonation
        );

        $subject->checkLoginCookie();
    }

    public function testRememberedCredentialChangesWithThePasswordAndOldCookieIsRejected(): void
    {
        $user = $this->mock(User::class);
        $registration = new UserRegistration($user)->setPassword('old-password-hash')->setEmail('test@example.test')->setCreationDate(12345);
        $user->shouldReceive('getId')->andReturn(101);
        $user->shouldReceive('getRegistration')->andReturn($registration);
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('find')->with(101)->once()->andReturn($user);
        $users->shouldNotReceive('save');
        $session = $this->mock(SessionInterface::class);
        $session->shouldNotReceive('setUser');
        $destruction = $this->mock(SessionDestructionInterface::class);
        $destruction->shouldReceive('destroySession')->with($session)->once();
        $hasher = $this->mock(StuHashInterface::class);
        $hasher->shouldReceive('hash')->andReturnUsing(fn (string $value): string => sha1($value));
        $impersonation = $this->mock(AdminImpersonationInterface::class);
        $impersonation->shouldReceive('isActive')->andReturnFalse();
        $subject = new SessionLogin(
            $this->mock(UserIpTableRepositoryInterface::class),
            $this->mock(SessionStringRepositoryInterface::class),
            $users,
            $this->mock(UserSettingsProviderInterface::class),
            $session,
            $destruction,
            $this->mock(StuConfigInterface::class),
            $hasher,
            $impersonation
        );
        $method = $this->getMethod($subject, 'buildCookieString');
        $oldCookie = $method->invoke($subject, $user);
        $registration->setPassword('new-password-hash');
        self::assertNotSame($oldCookie, $method->invoke($subject, $user));
        $previousSession = $_SESSION ?? [];
        $previousCookies = $_COOKIE;
        try {
            $_SESSION = ['uid' => 101];
            $_COOKIE['sstr'] = $oldCookie;
            $subject->checkLoginCookie();
        } finally {
            $_SESSION = $previousSession;
            $_COOKIE = $previousCookies;
        }
    }

    public function testAuthenticatedIndexRequestUsesSharedSessionValidation(): void
    {
        $session = $this->mock(SessionInterface::class);
        $session->shouldReceive('createSession')->once();
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldNotReceive('find', 'save');
        $impersonation = $this->mock(AdminImpersonationInterface::class);
        $impersonation->shouldReceive('isActive')->once()->andReturnFalse();
        $subject = new SessionLogin(
            $this->mock(UserIpTableRepositoryInterface::class),
            $this->mock(SessionStringRepositoryInterface::class),
            $users,
            $this->mock(UserSettingsProviderInterface::class),
            $session,
            $this->mock(SessionDestructionInterface::class),
            $this->mock(StuConfigInterface::class),
            $this->mock(StuHashInterface::class),
            $impersonation
        );
        $previousSession = $_SESSION ?? [];
        try {
            $_SESSION = ['uid' => 101, 'login' => 1];
            $subject->checkLoginCookie();
        } finally {
            $_SESSION = $previousSession;
        }
    }


    public function testSuccessfulAuthenticationBindsTheSessionCredential(): void
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(101);
        $user->shouldReceive('getRegistration')->andReturn(new UserRegistration($user)->setPassword('stored-password-hash'));
        $session = $this->mock(SessionInterface::class);
        $session->shouldReceive('setUser')->with($user)->once()->andReturnSelf();
        $subject = new SessionLogin(
            $this->mock(UserIpTableRepositoryInterface::class),
            $this->mock(SessionStringRepositoryInterface::class),
            $this->mock(UserRepositoryInterface::class),
            $this->mock(UserSettingsProviderInterface::class),
            $session,
            $this->mock(SessionDestructionInterface::class),
            $this->mock(StuConfigInterface::class),
            $this->mock(StuHashInterface::class),
            $this->mock(AdminImpersonationInterface::class)
        );
        $previousSession = $_SESSION ?? [];
        try {
            $_SESSION = [];
            $this->getMethod($subject, 'setUser')->invoke($subject, $user);
            self::assertSame(101, $_SESSION['uid']);
            self::assertSame(1, $_SESSION['login']);
            self::assertTrue(SessionCredential::isValid($user));
        } finally {
            $_SESSION = $previousSession;
        }
    }

}
