<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset;

use Mockery;
use Mockery\MockInterface;
use Stu\Component\Player\Deletion\PlayerDeletionInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\GameTurnInterface;
use Stu\Orm\Entity\HistoryInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\HistoryRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\StuTestCase;

class ResetManagerTest extends StuTestCase
{

    /**
     * @var null|MockInterface|PlayerDeletionInterface
     */
    private $playerDeletion;

    /**
     * @var null|MockInterface|ColonyRepositoryInterface
     */
    private $colonyRepository;

    /**
     * @var null|MockInterface|KnPostRepositoryInterface
     */
    private $knPostRepository;

    /**
     * @var null|MockInterface|HistoryRepositoryInterface
     */
    private $historyRepository;

    /**
     * @var null|MockInterface|GameTurnRepositoryInterface
     */
    private $gameTurnRepository;

    /**
     * @var null|MockInterface|RpgPlotRepositoryInterface
     */
    private $rpgPlotRepository;

    /**
     * @var null|MockInterface|ResetManagerInterface
     */
    private $manager;

    public function setUp(): void
    {
        $this->playerDeletion = $this->mock(PlayerDeletionInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->knPostRepository = $this->mock(KnPostRepositoryInterface::class);
        $this->historyRepository = $this->mock(HistoryRepositoryInterface::class);
        $this->gameTurnRepository = $this->mock(GameTurnRepositoryInterface::class);
        $this->rpgPlotRepository = $this->mock(RpgPlotRepositoryInterface::class);

        $this->manager = new ResetManager(
            $this->playerDeletion,
            $this->colonyRepository,
            $this->knPostRepository,
            $this->historyRepository,
            $this->gameTurnRepository,
            $this->rpgPlotRepository
        );
    }

    public function testPerformResetResets(): void
    {
        $this->playerDeletion->shouldReceive('handleReset')
            ->withNoArgs()
            ->once();

        $colony = $this->mock(ColonyInterface::class);

        $this->colonyRepository->shouldReceive('findAll')
            ->withNoArgs()
            ->once()
            ->andReturn([$colony]);
        $this->colonyRepository->shouldReceive('save')
            ->with($colony)
            ->once();

        $colony->shouldReceive('setMask')
            ->with(null)
            ->once();

        $post = $this->mock(KnPostInterface::class);

        $this->knPostRepository->shouldReceive('findAll')
            ->withNoArgs()
            ->once()
            ->andReturn([$post]);
        $this->knPostRepository->shouldReceive('delete')
            ->with($post)
            ->once();

        $entry = $this->mock(HistoryInterface::class);

        $this->historyRepository->shouldReceive('findAll')
            ->withNoArgs()
            ->once()
            ->andReturn([$entry]);
        $this->historyRepository->shouldReceive('delete')
            ->with($entry)
            ->once();

        $plot = $this->mock(RpgPlotInterface::class);

        $this->rpgPlotRepository->shouldReceive('findAll')
            ->withNoArgs()
            ->once()
            ->andReturn([$plot]);
        $this->rpgPlotRepository->shouldReceive('delete')
            ->with($plot)
            ->once();

        $existingTurn = $this->mock(GameTurnInterface::class);
        $newTurn = $this->mock(GameTurnInterface::class);

        $this->gameTurnRepository->shouldReceive('findAll')
            ->withNoArgs()
            ->once()
            ->andReturn([$existingTurn]);
        $this->gameTurnRepository->shouldReceive('delete')
            ->with($existingTurn)
            ->once();
        $this->gameTurnRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($newTurn);
        $this->gameTurnRepository->shouldReceive('save')
            ->with($newTurn)
            ->once();

        $newTurn->shouldReceive('setTurn')
            ->with(1)
            ->once()
            ->andReturnSelf();
        $newTurn->shouldReceive('setStart')
            ->with(Mockery::type('int'))
            ->once()
            ->andReturnSelf();
        $newTurn->shouldReceive('setEnd')
            ->with(0)
            ->once()
            ->andReturnSelf();

        $this->manager->performReset();
    }
}
