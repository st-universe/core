<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Common\Faction;

use Mockery\MockInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\StuApiV1TestCase;

class GetFactionsTest extends StuApiV1TestCase
{

    /**
     * @var null|MockInterface|FactionRepositoryInterface
     */
    private $factionRepository;

    public function setUp(): void
    {
        $this->factionRepository = $this->mock(FactionRepositoryInterface::class);

        $this->setUpApiHandler(
            new GetFactions(
                $this->factionRepository
            )
        );
    }

    public function testActionRespondsWithData(): void {
        $faction = $this->mock(FactionInterface::class);

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

        $this->response->shouldReceive('withData')
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

        $this->performAssertion();
    }
}
