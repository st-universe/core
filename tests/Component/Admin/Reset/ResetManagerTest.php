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
use Stu\Component\Admin\Reset\Alliance\AllianceResetInterface;
use Stu\Component\Admin\Reset\Communication\KnResetInterface;
use Stu\Component\Admin\Reset\Communication\PmResetInterface;
use Stu\Component\Admin\Reset\Crew\CrewResetInterface;
use Stu\Component\Admin\Reset\Fleet\FleetResetInterface;
use Stu\Component\Admin\Reset\Map\MapResetInterface;
use Stu\Component\Admin\Reset\Ship\ShipResetInterface;
use Stu\Component\Admin\Reset\Storage\StorageResetInterface;
use Stu\Component\Admin\Reset\Trade\TradeResetInterface;
use Stu\Component\Admin\Reset\User\UserResetInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Player\Deletion\PlayerDeletionInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\GameTurnInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\Orm\Repository\GameRequestRepositoryInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\HistoryRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class ResetManagerTest extends StuTestCase
{
    /** @var MockInterface&KnResetInterface */
    private MockInterface $knReset;

    /** @var MockInterface&PmResetInterface */
    private MockInterface $pmReset;

    /** @var MockInterface&AllianceResetInterface */
    private MockInterface $allianceReset;

    /** @var MockInterface&FleetResetInterface */
    private MockInterface $fleetReset;

    /** @var MockInterface&CrewResetInterface */
    private MockInterface $crewReset;

    /** @var MockInterface&ShipResetInterface */
    private MockInterface $shipReset;

    /** @var MockInterface&StorageResetInterface */
    private MockInterface $storageReset;

    /** @var MockInterface&MapResetInterface */
    private MockInterface $mapReset;

    /** @var MockInterface&TradeResetInterface */
    private MockInterface $tradeReset;

    /** @var MockInterface&UserResetInterface */
    private MockInterface $userReset;

    /** @var MockInterface&GameRequestRepositoryInterface */
    private MockInterface $gameRequestRepository;

    /** @var MockInterface&GameConfigRepositoryInterface */
    private MockInterface $gameConfigRepository;

    /** @var MockInterface&PlayerDeletionInterface */
    private MockInterface $playerDeletion;

    /** @var MockInterface&ColonyRepositoryInterface */
    private MockInterface $colonyRepository;

    /** @var MockInterface&HistoryRepositoryInterface */
    private MockInterface $historyRepository;

    /** @var MockInterface&GameTurnRepositoryInterface */
    private MockInterface $gameTurnRepository;

    /** @var MockInterface&PlanetFieldRepositoryInterface */
    private MockInterface $planetFieldRepository;

    /** @var MockInterface&StuConfigInterface */
    private MockInterface $stuConfig;

    /** @var MockInterface&EntityManagerInterface */
    private MockInterface $entityManager;

    /** @var MockInterface&Connection */
    private MockInterface $connection;

    private Interactor $interactor;

    private ResetManager $manager;

    public function setUp(): void
    {
        vfsStream::setup('tmpDir');

        $this->knReset = $this->mock(KnResetInterface::class);
        $this->pmReset = $this->mock(PmResetInterface::class);
        $this->allianceReset = $this->mock(AllianceResetInterface::class);
        $this->fleetReset = $this->mock(FleetResetInterface::class);
        $this->crewReset = $this->mock(CrewResetInterface::class);
        $this->shipReset = $this->mock(ShipResetInterface::class);
        $this->storageReset = $this->mock(StorageResetInterface::class);
        $this->mapReset = $this->mock(MapResetInterface::class);
        $this->tradeReset = $this->mock(TradeResetInterface::class);
        $this->userReset = $this->mock(UserResetInterface::class);
        $this->gameRequestRepository = $this->mock(GameRequestRepositoryInterface::class);

        $this->gameConfigRepository = $this->mock(GameConfigRepositoryInterface::class);
        $this->playerDeletion = $this->mock(PlayerDeletionInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->historyRepository = $this->mock(HistoryRepositoryInterface::class);
        $this->gameTurnRepository = $this->mock(GameTurnRepositoryInterface::class);
        $this->planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $this->stuConfig = $this->mock(StuConfigInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->connection = $this->mock(Connection::class);

        $this->interactor = new Interactor(null, vfsStream::url('tmpDir') . '/foo');

        $this->manager = new ResetManager(
            $this->knReset,
            $this->pmReset,
            $this->allianceReset,
            $this->fleetReset,
            $this->crewReset,
            $this->shipReset,
            $this->storageReset,
            $this->mapReset,
            $this->tradeReset,
            $this->userReset,
            $this->gameRequestRepository,
            $this->gameConfigRepository,
            $this->playerDeletion,
            $this->colonyRepository,
            $this->historyRepository,
            $this->gameTurnRepository,
            $this->planetFieldRepository,
            $this->stuConfig,
            $this->entityManager,
            $this->connection
        );
    }

    public function testPerformResetResets(): void
    {
        $database = $this->mock(Connection::class);
        $colony = $this->mock(ColonyInterface::class);

        $this->storageReset->shouldReceive('deleteAllTradeOffers')
            ->withNoArgs()
            ->once();
        $this->storageReset->shouldReceive('deleteAllTorpedoStorages')
            ->withNoArgs()
            ->once();
        $this->storageReset->shouldReceive('deleteAllStorages')
            ->withNoArgs()
            ->once();

        $this->crewReset->shouldReceive('deleteAllCrew')
            ->withNoArgs()
            ->once();

        $this->fleetReset->shouldReceive('deleteAllFleets')
            ->withNoArgs()
            ->once();

        $this->knReset->shouldReceive('resetKn')
            ->withNoArgs()
            ->once();

        $this->pmReset->shouldReceive('unsetAllInboxReferences')
            ->withNoArgs()
            ->once();
        $this->pmReset->shouldReceive('resetAllNonNpcPmFolders')
            ->withNoArgs()
            ->once();
        $this->pmReset->shouldReceive('resetPms')
            ->withNoArgs()
            ->once();
        $this->pmReset->shouldReceive('deleteAllContacts')
            ->withNoArgs()
            ->once();

        $this->allianceReset->shouldReceive('deleteAllAllianceBoards')
            ->withNoArgs()
            ->once();
        $this->allianceReset->shouldReceive('deleteAllAllianceJobs')
            ->withNoArgs()
            ->once();
        $this->allianceReset->shouldReceive('deleteAllAllianceRelations')
            ->withNoArgs()
            ->once();
        $this->allianceReset->shouldReceive('deleteAllAlliances')
            ->withNoArgs()
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

        $this->mapReset->shouldReceive('deleteAllUserMaps')
            ->withNoArgs()
            ->once();
        $this->mapReset->shouldReceive('deleteAllFlightSignatures')
            ->withNoArgs()
            ->once();
        $this->mapReset->shouldReceive('deleteAllAstroEntries')
            ->withNoArgs()
            ->once();
        $this->mapReset->shouldReceive('deleteAllColonyScans')
            ->withNoArgs()
            ->once();
        $this->mapReset->shouldReceive('deleteAllTachyonScans')
            ->withNoArgs()
            ->once();
        $this->mapReset->shouldReceive('deleteAllUserLayers')
            ->withNoArgs()
            ->once();

        $this->tradeReset->shouldReceive('deleteAllBasicTrades')
            ->withNoArgs()
            ->once();
        $this->tradeReset->shouldReceive('deleteAllDeals')
            ->withNoArgs()
            ->once();
        $this->tradeReset->shouldReceive('deleteAllLotteryTickets')
            ->withNoArgs()
            ->once();
        $this->tradeReset->shouldReceive('deleteAllTradeShoutboxEntries')
            ->withNoArgs()
            ->once();
        $this->tradeReset->shouldReceive('deleteAllTradeTransactions')
            ->withNoArgs()
            ->once();

        $this->userReset->shouldReceive('deletePirateWrathEntries')
            ->withNoArgs()
            ->once();
        $this->userReset->shouldReceive('archiveBlockedUsers')
            ->withNoArgs()
            ->once();
        $this->userReset->shouldReceive('deleteAllDatabaseUserEntries')
            ->withNoArgs()
            ->once();
        $this->userReset->shouldReceive('deleteAllNotes')
            ->withNoArgs()
            ->once();
        $this->userReset->shouldReceive('deleteAllPrestigeLogs')
            ->withNoArgs()
            ->once();
        $this->userReset->shouldReceive('resetNpcs')
            ->withNoArgs()
            ->once();
        $this->userReset->shouldReceive('deleteAllUserInvitations')
            ->withNoArgs()
            ->once();
        $this->userReset->shouldReceive('deleteAllUserIpTableEntries')
            ->withNoArgs()
            ->once();
        $this->userReset->shouldReceive('deleteAllUserProfileVisitors')
            ->withNoArgs()
            ->once();

        $this->gameRequestRepository->shouldReceive('truncateAllGameRequests')
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
        $this->entityManager->shouldReceive('getConnection')
            ->withNoArgs()
            ->once()
            ->andReturn($database);

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

        $colony->shouldReceive('setMask')
            ->with(null)
            ->once();

        $this->historyRepository->shouldReceive('truncateAllEntities')
            ->withNoArgs()
            ->once();

        $newTurn = $this->mock(GameTurnInterface::class);

        $this->gameTurnRepository->shouldReceive('truncateAllGameTurns')
            ->withNoArgs()
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

        $this->storageReset->shouldReceive('deleteAllTradeOffers')
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
            ->with(GameEnum::CONFIG_GAMESTATE_VALUE_RESET, $this->connection)
            ->once();

        $this->manager->performReset($this->interactor);
    }
}
