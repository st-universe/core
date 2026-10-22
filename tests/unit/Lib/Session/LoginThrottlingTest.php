<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Lib\Cache\PrivateCacheDirectory;
use Stu\Lib\LoginException;
use Stu\Lib\Request\RequestLimitException;
use Stu\Lib\Request\RequestRateLimiter;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserIpTable;
use Stu\Orm\Entity\UserRegistration;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserIpTableRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class LoginThrottlingTest extends StuTestCase
{
    private string $directory;
    private int $now = 1000;
    private RequestRateLimiter $limiter;
    private array $server;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/stu-login-test-' . bin2hex(random_bytes(8));
        $time = $this->mock(StuTime::class);
        $time->shouldReceive('time')->andReturnUsing(fn () => $this->now);
        $this->limiter = new RequestRateLimiter(new PrivateCacheDirectory($this->directory), $time);
        $this->server = $_SERVER;
        $_SERVER['REMOTE_ADDR'] = '192.0.2.1';
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->server;
        foreach (glob($this->directory . '/*') ?: [] as $path) {
            unlink($path);
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    private function subject(bool $successful = false): SessionLogin
    {
        $user = $this->mock(User::class);
        $registration = new UserRegistration($user)->setPassword(password_hash('valid-password', PASSWORD_DEFAULT));
        $user->shouldReceive('getId')->andReturn(101);
        $user->shouldReceive('getRegistration')->andReturn($registration);
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('getByLogin')->with('testlogin')->andReturn($user);
        $users->shouldReceive('getByLogin')->with('101')->andReturnNull();
        $users->shouldReceive('find')->with(101)->andReturn($user);
        $config = $this->mock(StuConfigInterface::class);
        $config->shouldReceive('getSecuritySettings->getMasterPassword')->andReturnNull();
        $session = $this->mock(SessionInterface::class);
        $strings = $this->mock(SessionStringRepositoryInterface::class);
        $settings = $this->mock(UserSettingsProviderInterface::class);
        $impersonation = $this->mock(AdminImpersonationInterface::class);
        $ips = $this->mock(UserIpTableRepositoryInterface::class);
        if ($successful) {
            $user->shouldReceive('isLocked')->andReturnFalse();
            $user->shouldReceive('getState')->andReturn(UserStateEnum::ACTIVE);
            $user->shouldReceive('isVacationMode')->andReturnFalse();
            $users->shouldReceive('save')->with($user)->once();
            $session->shouldReceive('setUser')->with($user)->once()->andReturnSelf();
            $strings->shouldReceive('truncate')->with($user)->once();
            $settings->shouldReceive('isSaveLogin')->with($user)->andReturnTrue();
            $impersonation->shouldReceive('stop')->once();
            $ips->shouldReceive('prototype')->once()->andReturn(new UserIpTable());
            $ips->shouldReceive('save')->once();
        } else {
            $users->shouldNotReceive('save');
            $session->shouldNotReceive('setUser');
        }
        return new SessionLogin($ips, $strings, $users, $settings, $session, $this->mock(SessionDestructionInterface::class), $config, $this->mock(StuHashInterface::class), $impersonation, $this->limiter);
    }

    public function testDistributedAliasesCannotBypassTheAccountBudget(): void
    {
        $subject = $this->subject();
        for ($index = 0; $index < 5; $index++) {
            $_SERVER['REMOTE_ADDR'] = '192.0.2.' . ($index + 1);
            try {
                $subject->login($index % 2 === 0 ? ' TestLogin ' : '101', 'wrong-password');
                self::fail('Falsches Passwort akzeptiert');
            } catch (LoginException) {
                self::assertTrue(true);
            }
        }
        $_SERVER['REMOTE_ADDR'] = '192.0.2.99';
        $this->expectException(RequestLimitException::class);
        $subject->login('101', 'wrong-password');
    }

    public function testManyUnknownAccountsFromOneSourceAreLimitedBeforeFurtherLookups(): void
    {
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('getByLogin')->times(30)->andReturnNull();
        $subject = new SessionLogin($this->mock(UserIpTableRepositoryInterface::class), $this->mock(SessionStringRepositoryInterface::class), $users, $this->mock(UserSettingsProviderInterface::class), $this->mock(SessionInterface::class), $this->mock(SessionDestructionInterface::class), $this->mock(StuConfigInterface::class), $this->mock(StuHashInterface::class), $this->mock(AdminImpersonationInterface::class), $this->limiter);
        for ($index = 0; $index < 30; $index++) {
            try {
                $subject->login('unknown' . $index, 'wrong');
            } catch (LoginException) {
                self::assertTrue(true);
            }
        }
        $this->expectException(RequestLimitException::class);
        $subject->login('anotherunknown', 'wrong');
    }

    public function testCorrectCredentialsWorkAfterExpiryAndResetOnlyTheirOwnBudget(): void
    {
        for ($index = 0; $index < 5; $index++) {
            $this->limiter->consume('login-name', 'testlogin', 5, 60);
            $this->limiter->consume('login-account', '101', 5, 60);
        }
        $this->now += 61;
        $subject = $this->subject(true);
        self::assertTrue($subject->login('TestLogin', 'valid-password'));
        self::assertSame(101, $_SESSION['uid']);
        for ($index = 0; $index < 5; $index++) {
            $this->limiter->consume('login-account', '101', 5, 60);
        }
        self::assertTrue(true);
    }
}
