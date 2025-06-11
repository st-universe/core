<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Lib\AccountNotVerifiedException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class AccessCheckTest extends StuTestCase
{
    private MockInterface&StuConfigInterface $stuConfig;
    private MockInterface&GameControllerInterface $game;

    private AccessCheckInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->stuConfig = $this->mock(StuConfigInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);

        $this->subject = new AccessCheck($this->stuConfig);
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
            ->andReturn(UserEnum::USER_STATE_ACCOUNT_VERIFICATION);

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
            ->andReturn(UserEnum::USER_STATE_ACTIVE);

        $result = $this->subject->checkUserAccess($controller, $this->game);

        $this->assertTrue($result);
    }

    public function testCheckUserAccessExpectTrueWhenUserIsAdmin(): void
    {
        $controller = $this->mock(AccessCheckControllerInterface::class);
        $user = $this->mock(UserInterface::class);

        $controller->shouldReceive('getFeatureIdentifier')
            ->withNoArgs()
            ->once()
            ->andReturn(AccessGrantedFeatureEnum::COLONY_SANDBOX);

        $user->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(UserEnum::USER_STATE_ACTIVE);
        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->game->shouldReceive('hasUser')
            ->withNoArgs()
            ->once()
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
        $user = $this->mock(UserInterface::class);

        $controller->shouldReceive('getFeatureIdentifier')
            ->withNoArgs()
            ->once()
            ->andReturn(AccessGrantedFeatureEnum::COLONY_SANDBOX);

        $user->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(UserEnum::USER_STATE_ACTIVE);
        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->game->shouldReceive('hasUser')
            ->withNoArgs()
            ->once()
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
        $user = $this->mock(UserInterface::class);

        $controller->shouldReceive('getFeatureIdentifier')
            ->withNoArgs()
            ->once()
            ->andReturn(AccessGrantedFeatureEnum::COLONY_SANDBOX);

        $user->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(UserEnum::USER_STATE_ACTIVE);
        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->game->shouldReceive('hasUser')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->game->shouldReceive('addInformation')
            ->with('[b][color=#ff2626]Aktion nicht möglich, Spieler ist nicht berechtigt![/color][/b]')
            ->once();

        $this->stuConfig->shouldReceive('getGameSettings->getGrantedFeatures')
            ->withNoArgs()
            ->once()
            ->andReturn($grantedFeatures);

        $result = $this->subject->checkUserAccess($controller, $this->game);

        $this->assertFalse($result);
    }
}
