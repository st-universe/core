<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Player\Deletion\PlayerDeletionInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\GameTurnInterface;
use Stu\Orm\Entity\HistoryInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Entity\RpgPlotMemberInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\HistoryRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\StuTestCase;

class ResetManagerTest extends StuTestCase
{
    /** @var MockInterface&GameConfigRepositoryInterface */
    private MockInterface $gameConfigRepository;

    /** @var MockInterface&PlayerDeletionInterface */
    private MockInterface $playerDeletion;

    /** @var MockInterface&ColonyRepositoryInterface */
    private MockInterface $colonyRepository;

    /** @var MockInterface&KnPostRepositoryInterface */
    private MockInterface $knPostRepository;

    /** @var MockInterface&HistoryRepositoryInterface */
    private MockInterface $historyRepository;

    /** @var MockInterface&GameTurnRepositoryInterface */
    private MockInterface $gameTurnRepository;

    /** @var MockInterface&RpgPlotRepositoryInterface */
    private MockInterface $rpgPlotRepository;

    /** @var MockInterface&RpgPlotMemberRepositoryInterface */
    private MockInterface $rpgPlotMemberRepository;

    /** @var MockInterface&PlanetFieldRepositoryInterface */
    private MockInterface $planetFieldRepository;

    /** @var MockInterface&StuConfigInterface */
    private MockInterface $stuConfig;

    /** @var MockInterface&EntityManagerInterface */
    private MockInterface $entityManager;

    private ResetManager $manager;

    public function setUp(): void
    {
        $this->gameConfigRepository = $this->mock(GameConfigRepositoryInterface::class);
        $this->playerDeletion = $this->mock(PlayerDeletionInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->knPostRepository = $this->mock(KnPostRepositoryInterface::class);
        $this->historyRepository = $this->mock(HistoryRepositoryInterface::class);
        $this->gameTurnRepository = $this->mock(GameTurnRepositoryInterface::class);
        $this->rpgPlotRepository = $this->mock(RpgPlotRepositoryInterface::class);
        $this->rpgPlotMemberRepository = $this->mock(RpgPlotMemberRepositoryInterface::class);
        $this->planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $this->stuConfig = $this->mock(StuConfigInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->manager = new ResetManager(
            $this->gameConfigRepository,
            $this->playerDeletion,
            $this->colonyRepository,
            $this->knPostRepository,
            $this->historyRepository,
            $this->gameTurnRepository,
            $this->rpgPlotMemberRepository,
            $this->rpgPlotRepository,
            $this->planetFieldRepository,
            $this->stuConfig,
            $this->entityManager
        );
    }

    public function testPerformResetResets(): void
    {
        $database = $this->mock(Connection::class);
        $colony = $this->mock(ColonyInterface::class);

        $adminId = 666;

        $this->playerDeletion->shouldReceive('handleReset')
            ->withNoArgs()
            ->once();

        $this->stuConfig->shouldReceive('getResetSettings->getDelayInSeconds')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $this->stuConfig->shouldReceive('getGameSettings->getAdminSettings->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($adminId);

        $this->entityManager->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->once();
        $this->entityManager->shouldReceive('commit')
            ->withNoArgs()
            ->once();
        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();
        $this->entityManager->shouldReceive('getConnection')
            ->withNoArgs()
            ->once()
            ->andReturn($database);

        $database->shouldReceive('executeQuery')
            ->with(
                sprintf(
                    'ALTER SEQUENCE stu_user_id_seq RESTART WITH %d',
                    $adminId
                )
            )
            ->once();

        $this->colonyRepository->shouldReceive('findAll')
            ->withNoArgs()
            ->once()
            ->andReturn([$colony]);
        $this->colonyRepository->shouldReceive('save')
            ->with($colony)
            ->once();

        $this->planetFieldRepository->shouldReceive('truncateByColony')
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

        $plotMember = $this->mock(RpgPlotMemberInterface::class);

        $this->rpgPlotMemberRepository->shouldReceive('findAll')
            ->withNoArgs()
            ->once()
            ->andReturn([$plotMember]);
        $this->rpgPlotMemberRepository->shouldReceive('delete')
            ->with($plotMember)
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

        $this->gameConfigRepository->shouldReceive('updateGameState')
            ->with(GameEnum::CONFIG_GAMESTATE_VALUE_RESET)
            ->once();
        $this->gameConfigRepository->shouldReceive('updateGameState')
            ->with(GameEnum::CONFIG_GAMESTATE_VALUE_ONLINE)
            ->once();

        $this->manager->performReset();
    }


    public function testPerformResetRollsBackAndThrowsError(): void
    {
        $error = 'some-error';

        static::expectException(Exception::class);
        static::expectExceptionMessage($error);

        $this->stuConfig->shouldReceive('getResetSettings->getDelayInSeconds')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $this->playerDeletion->shouldReceive('handleReset')
            ->withNoArgs()
            ->once()
            ->andThrow(new Exception($error));

        $this->entityManager->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->once();
        $this->entityManager->shouldReceive('rollback')
            ->withNoArgs()
            ->once();

        $this->gameConfigRepository->shouldReceive('updateGameState')
            ->with(GameEnum::CONFIG_GAMESTATE_VALUE_RESET)
            ->once();

        $this->manager->performReset();
    }
}
