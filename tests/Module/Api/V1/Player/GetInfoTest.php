<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Orm\Entity\UserInterface;

class GetInfoTest extends MockeryTestCase
{

    /**
     * @var null|MockInterface|SessionInterface
     */
    private $session;

    /**
     * @var null|GetInfo
     */
    private $handler;

    public function setUp(): void
    {
        $this->session = Mockery::mock(SessionInterface::class);

        $this->handler = new GetInfo(
            $this->session
        );
    }

    public function testActionReturnsPlayerData(): void
    {
        $user = Mockery::mock(UserInterface::class);
        $request = Mockery::mock(ServerRequestInterface::class);
        $response = Mockery::mock(JsonResponseInterface::class);

        $userId = 666;
        $factionId = 42;
        $allianceId = null;
        $avatarPath = 'some/path/to/nowhere';
        $name = 'some-user-name';

        $this->session->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $user->shouldReceive('getFaction->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($factionId);
        $user->shouldReceive('getAllianceId')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceId);
        $user->shouldReceive('getFullAvatarPath')
            ->withNoArgs()
            ->once()
            ->andReturn($avatarPath);
        $user->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($name);

        $response->shouldReceive('withData')
            ->with([
                'id' => $userId,
                'faction_id' => $factionId,
                'name' => $name,
                'alliance_id' => $allianceId,
                'avatar_path' => $avatarPath
            ])
            ->once()
            ->andReturnSelf();

        $this->assertSame(
            $response,
            call_user_func($this->handler, $request, $response, [])
        );
    }
}
