<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player;

use Mockery\MockInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuApiV1TestCase;

class GetInfoTest extends StuApiV1TestCase
{

    /**
     * @var null|MockInterface|SessionInterface
     */
    private $session;

    public function setUp(): void
    {
        $this->session = $this->mock(SessionInterface::class);

        parent::setUpApiHandler(
            new GetInfo(
                $this->session
            )
        );
    }

    public function testActionReturnsPlayerData(): void
    {
        $user = $this->mock(UserInterface::class);

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
        $user->shouldReceive('getUserName')
            ->withNoArgs()
            ->once()
            ->andReturn($name);

        $this->response->shouldReceive('withData')
            ->with([
                'id' => $userId,
                'faction_id' => $factionId,
                'name' => $name,
                'alliance_id' => $allianceId,
                'avatar_path' => $avatarPath
            ])
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }
}
