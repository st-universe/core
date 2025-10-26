<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\Lib\AccountNotVerifiedException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\StuTestCase;

class AccessCheckTest extends StuTestCase
{
    private MockInterface&SessionStringRepositoryInterface $sessionStringRepository;
    private MockInterface&StuConfigInterface $stuConfig;

    private MockInterface&GameControllerInterface $game;

    private AccessCheckInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->sessionStringRepository = $this->mock(SessionStringRepositoryInterface::class);
        $this->stuConfig = $this->mock(StuConfigInterface::class);

        $this->game = $this->mock(GameControllerInterface::class);

        $this->subject = new AccessCheck(
            $this->sessionStringRepository,
            $this->stuConfig
        );
    }

    public function testCheckUserAccessExpectTrueWhenNoAccessCheckController(): void
    {
        $controller = $this->mock(NoAccessCheckControllerInterface::class);

        $result = $this->subject->checkUserAccess($controller, $this->game);

        $this->assertTrue($result);
    }

    public function testCheckUserAccessExpectExceptionWhenAccountNotVerified(): void
    {
        static::expectException(AccountNotVerifiedException::class);

        $this->game->shouldReceive('hasUser')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->game->shouldReceive('getUser->getState')
            ->withNoArgs()
            ->once()
            ->andReturn(UserStateEnum::ACCOUNT_VERIFICATION);

        $controller = $this->mock(ControllerInterface::class);

        $this->subject->checkUserAccess($controller, $this->game);
    }

    public function testCheckUserAccessExpectTrueWhenNoAccessCheckNeeded(): void
    {
        $controller = $this->mock(ControllerInterface::class);

        $this->game->shouldReceive('hasUser')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->game->shouldReceive('getUser->getState')
            ->withNoArgs()
            ->once()
            ->andReturn(UserStateEnum::ACTIVE);

        $result = $this->subject->checkUserAccess($controller, $this->game);

        $this->assertTrue($result);
    }

    public function testCheckUserAccessExpectTrueWhenUserIsAdmin(): void
    {
        $controller = $this->mock(AccessCheckControllerInterface::class);
        $user = $this->mock(User::class);

        $controller->shouldReceive('getFeatureIdentifier')
            ->withNoArgs()
            ->once()
            ->andReturn(AccessGrantedFeatureEnum::COLONY_SANDBOX);

        $user->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(UserStateEnum::ACTIVE);
        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->game->shouldReceive('hasUser')
            ->withNoArgs()
            ->andReturn(true);
        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->checkUserAccess($controller, $this->game);

        $this->assertTrue($result);
    }

    public function testCheckUserAccessExpectTrueWhenAccessGranted(): void
    {
        $controller = $this->mock(AccessCheckControllerInterface::class);
        $user = $this->mock(User::class);

        $controller->shouldReceive('getFeatureIdentifier')
            ->withNoArgs()
            ->once()
            ->andReturn(AccessGrantedFeatureEnum::COLONY_SANDBOX);

        $user->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(UserStateEnum::ACTIVE);
        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->game->shouldReceive('hasUser')
            ->withNoArgs()
            ->andReturn(true);
        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->stuConfig->shouldReceive('getGameSettings->getGrantedFeatures')
            ->withNoArgs()
            ->once()
            ->andReturn([['feature' => 'COLONY_SANDBOX', 'userIds' => [42]]]);

        $result = $this->subject->checkUserAccess($controller, $this->game);

        $this->assertTrue($result);
    }

    public static function provideAccessNotGrantedData(): array
    {
        return [
            [[['feature' => 'COLONY_SANDBOX', 'userIds' => [41, 43]]]],
            [[['feature' => 'COLONY_SANDBOX', 'userIds' => []]]],
            [[['feature' => 'UNKNOWN', 'userIds' => [42]]]],
            [[]],
        ];
    }

    #[DataProvider('provideAccessNotGrantedData')]
    public function testCheckUserAccessExpectFalseWhenAccessNotGranted(
        array $grantedFeatures
    ): void {
        $controller = $this->mock(AccessCheckControllerInterface::class);
        $user = $this->mock(User::class);

        $controller->shouldReceive('getFeatureIdentifier')
            ->withNoArgs()
            ->once()
            ->andReturn(AccessGrantedFeatureEnum::COLONY_SANDBOX);

        $user->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(UserStateEnum::ACTIVE);
        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->game->shouldReceive('hasUser')
            ->withNoArgs()
            ->andReturn(true);
        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->game->shouldReceive('getInfo->addInformation')
            ->with('[b][color=#ff2626]Aktion nicht möglich, Spieler ist nicht berechtigt![/color][/b]')
            ->once();

        $this->stuConfig->shouldReceive('getGameSettings->getGrantedFeatures')
            ->withNoArgs()
            ->once()
            ->andReturn($grantedFeatures);

        $result = $this->subject->checkUserAccess($controller, $this->game);

        $this->assertFalse($result);
    }

    public static function providerActionControllerData(): array
    {
        return [
            [false, 'SESSION_STRING', Mockery::mock(User::class), true],
            [true, 'SESSION_STRING', Mockery::mock(User::class), true],
            [true, null, Mockery::mock(User::class), false],
            [true, 'SESSION_STRING', null, false],
            [true, null, null, false],
            [false, null, null, true],
        ];
    }

    /** @param null|User|MockInterface $user*/
    #[DataProvider('providerActionControllerData')]
    public function testCheckUserAccessForActionControllers(
        bool $performSessionCheck,
        ?string $sstr,
        ?User $user,
        bool $expectedResult
    ): void {
        $controller = $this->mock(ActionControllerInterface::class);

        if ($sstr !== null) {
            request::setMockVars(['sstr' => $sstr]);
        }

        $controller->shouldReceive('performSessionCheck')
            ->withNoArgs()
            ->andReturn($performSessionCheck);

        if ($user !== null) {
            $user->shouldReceive('getState')
                ->withNoArgs()
                ->once()
                ->andReturn(UserStateEnum::ACTIVE);
            $user->shouldReceive('getId')
                ->withNoArgs()
                ->andReturn(42);
        }

        $this->game->shouldReceive('hasUser')
            ->withNoArgs()
            ->andReturn($user !== null);
        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $this->sessionStringRepository->shouldReceive('isValid')
            ->with($sstr, 42)
            ->zeroOrMoreTimes()
            ->andReturn($expectedResult);

        $result = $this->subject->checkUserAccess($controller, $this->game);

        $this->assertEquals($expectedResult, $result);
    }
}
