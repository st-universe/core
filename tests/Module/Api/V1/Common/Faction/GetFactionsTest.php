<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Common\Faction;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

class GetFactionsTest extends MockeryTestCase
{

    /**
     * @var null|MockInterface|FactionRepositoryInterface
     */
    private $factionRepository;

    /**
     * @var null|GetFactions
     */
    private $handler;

    public function setUp(): void
    {
        $this->factionRepository = Mockery::mock(FactionRepositoryInterface::class);

        $this->handler = new GetFactions(
            $this->factionRepository
        );
    }

    public function testActionRespondsWithData(): void {
        $faction = Mockery::mock(FactionInterface::class);
        $request = Mockery::mock(ServerRequestInterface::class);
        $response = Mockery::mock(JsonResponseInterface::class);

        $id = 12345;
        $name = 'some-name';
        $description = 'maty had a little lamb';
        $playerLimit = 666;
        $playerAmount = 42;

        $faction->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($id);
        $faction->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn($name);
        $faction->shouldReceive('getDescription')
            ->withNoArgs()
            ->once()
            ->andReturn($description);
        $faction->shouldReceive('getPlayerLimit')
            ->withNoArgs()
            ->once()
            ->andReturn($playerLimit);
        $faction->shouldReceive('getPlayerAmount')
            ->withNoArgs()
            ->once()
            ->andReturn($playerAmount);

        $this->factionRepository->shouldReceive('getByChooseable')
            ->with(true)
            ->once()
            ->andReturn([$faction]);

        $response->shouldReceive('withData')
            ->with([
                [
                    'id' => $id,
                    'name' => $name,
                    'description' => $description,
                    'player_limit' => $playerLimit,
                    'player_amount' => $playerAmount,
                ]
            ])
            ->once()
            ->andReturnSelf();

        $this->assertSame(
            $response,
            call_user_func($this->handler, $request, $response, [])
        );
    }
}
