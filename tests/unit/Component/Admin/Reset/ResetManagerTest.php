<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset;

use Ahc\Cli\IO\Interactor;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use Override;
use Stu\Component\Admin\Reset\Alliance\AllianceResetInterface;
use Stu\Component\Admin\Reset\Communication\PmResetInterface;
use Stu\Component\Admin\Reset\Fleet\FleetResetInterface;
use Stu\Component\Admin\Reset\Ship\ShipResetInterface;
use Stu\Component\Admin\Reset\User\UserResetInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Player\Deletion\PlayerDeletionInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\GameTurn;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class ResetManagerTest extends StuTestCase
{
    private MockInterface&PmResetInterface $pmReset;
    private MockInterface&AllianceResetInterface $allianceReset;
    private MockInterface&FleetResetInterface $fleetReset;
    private MockInterface&ShipResetInterface $shipReset;
    private MockInterface&UserResetInterface $userReset;
    private MockInterface&GameConfigRepositoryInterface $gameConfigRepository;
    private MockInterface&PlayerDeletionInterface $playerDeletion;
    private MockInterface&ColonyRepositoryInterface $colonyRepository;
    private MockInterface&GameTurnRepositoryInterface $gameTurnRepository;
    private MockInterface&PlanetFieldRepositoryInterface $planetFieldRepository;
    private MockInterface&EntityReset $entityReset;
    private MockInterface&SequenceResetInterface $sequenceReset;

    private MockInterface&StuConfigInterface $stuConfig;

    private MockInterface&EntityManagerInterface $entityManager;

    private MockInterface&Connection $connection;

    private Interactor $interactor;

    private ResetManager $manager;

    #[Override]
    public function setUp(): void
    {
        vfsStream::setup('tmpDir');

        $this->pmReset = $this->mock(PmResetInterface::class);
        $this->allianceReset = $this->mock(AllianceResetInterface::class);
        $this->fleetReset = $this->mock(FleetResetInterface::class);
        $this->shipReset = $this->mock(ShipResetInterface::class);
        $this->userReset = $this->mock(UserResetInterface::class);

        $this->gameConfigRepository = $this->mock(GameConfigRepositoryInterface::class);
        $this->playerDeletion = $this->mock(PlayerDeletionInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->gameTurnRepository = $this->mock(GameTurnRepositoryInterface::class);
        $this->planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $this->entityReset = $this->mock(EntityReset::class);
        $this->sequenceReset = $this->mock(SequenceResetInterface::class);
        $this->stuConfig = $this->mock(StuConfigInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->connection = $this->mock(Connection::class);
        $this->userReset = $this->mock(UserResetInterface::class);


        $this->interactor = new Interactor(null, vfsStream::url('tmpDir') . '/foo');

        $this->manager = new ResetManager(
            $this->pmReset,
            $this->allianceReset,
            $this->fleetReset,
            $this->shipReset,
            $this->userReset,
            $this->gameConfigRepository,
            $this->playerDeletion,
            $this->colonyRepository,
            $this->gameTurnRepository,
            $this->planetFieldRepository,
            $this->entityReset,
            $this->sequenceReset,
            $this->stuConfig,
            $this->entityManager,
            $this->connection
        );
    }

    public function testPerformResetResets(): void
    {
        $database = $this->mock(Connection::class);
        $colony = $this->mock(Colony::class);

        $this->fleetReset->shouldReceive('unsetShipFleetReference')
            ->withNoArgs()
            ->once();

        $this->pmReset->shouldReceive('unsetAllInboxReferences')
            ->withNoArgs()
            ->once();
        $this->pmReset->shouldReceive('resetAllNonNpcPmFolders')
            ->withNoArgs()
            ->once();

        $this->allianceReset->shouldReceive('unsetUserAlliances')
            ->withNoArgs()
            ->once();

        $this->entityReset->shouldReceive('reset')
            ->withAnyArgs()
            ->once();

        $this->shipReset->shouldReceive('undockAllDockedShips')
            ->withNoArgs()
            ->once();
        $this->shipReset->shouldReceive('deactivateAllTractorBeams')
            ->withNoArgs()
            ->once();
        $this->shipReset->shouldReceive('deleteAllTradeposts')
            ->withNoArgs()
            ->once();
        $this->shipReset->shouldReceive('deleteAllShips')
            ->withNoArgs()
            ->once();
        $this->shipReset->shouldReceive('deleteAllBuildplans')
            ->withNoArgs()
            ->once();

        $this->userReset->shouldReceive('archiveBlockedUsers')
            ->withNoArgs()
            ->once();
        $this->userReset->shouldReceive('resetNpcs')
            ->withNoArgs()
            ->once();

        $this->playerDeletion->shouldReceive('handleReset')
            ->withNoArgs()
            ->once();

        $this->stuConfig->shouldReceive('getResetSettings->getDelayInSeconds')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $this->entityManager->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->once();
        $this->entityManager->shouldReceive('commit')
            ->withNoArgs()
            ->once();
        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();

        $result = $this->mock(Result::class);

        $database->shouldReceive('executeQuery')
            ->withAnyArgs()
            ->zeroOrMoreTimes()
            ->andReturn($result);

        $result->shouldReceive('fetchOne')
            ->withAnyArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);

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

        $this->sequenceReset->shouldReceive('resetSequences')
            ->withNoArgs()
            ->once();

        $colony->shouldReceive('setMask')
            ->with(null)
            ->once();

        $newTurn = $this->mock(GameTurn::class);

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
            ->with(GameEnum::CONFIG_GAMESTATE_VALUE_RESET, $this->connection)
            ->once();

        $this->manager->performReset($this->interactor);
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

        $this->pmReset->shouldReceive('unsetAllInboxReferences')
            ->withAnyArgs()
            ->once()
            ->andThrow(new Exception($error));

        $this->entityManager->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->once();
        $this->entityManager->shouldReceive('rollback')
            ->withNoArgs()
            ->once();

        $this->gameConfigRepository->shouldReceive('updateGameState')
            ->with(GameEnum::CONFIG_GAMESTATE_VALUE_RESET, $this->connection)
            ->once();

        $this->manager->performReset($this->interactor);
    }
}
