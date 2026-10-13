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
}
