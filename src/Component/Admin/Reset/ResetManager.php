<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset;

use Override;
use Ahc\Cli\IO\Interactor;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
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
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\Orm\Repository\GameRequestRepositoryInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\HistoryRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Throwable;

final class ResetManager implements ResetManagerInterface
{
    public function __construct(
        private KnResetInterface $knReset,
        private PmResetInterface $pmReset,
        private AllianceResetInterface $allianceReset,
        private FleetResetInterface $fleetReset,
        private CrewResetInterface $crewReset,
        private ShipResetInterface $shipReset,
        private StorageResetInterface $storageReset,
        private MapResetInterface $mapReset,
        private TradeResetInterface $tradeReset,
        private UserResetInterface $userReset,
        private GameRequestRepositoryInterface $gameRequestRepository,
        private GameConfigRepositoryInterface $gameConfigRepository,
        private PlayerDeletionInterface $playerDeletion,
        private ColonyRepositoryInterface $colonyRepository,
        private HistoryRepositoryInterface $historyRepository,
        private GameTurnRepositoryInterface $gameTurnRepository,
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private StuConfigInterface $stuConfig,
        private EntityManagerInterface $entityManager,
        private Connection $connection
    ) {
    }

    #[Override]
    public function performReset(Interactor $io): void
    {
        $io->info('starting game reset', true);

        $startTime = hrtime(true);

        $this->setGameState(GameEnum::CONFIG_GAMESTATE_VALUE_RESET, $io);

        //wait for other processes (e.g. ticks) to finish
        $io->info('  - starting sleep, so other processes can finish (e.g. ticks)', true);
        $sleepCountdownInSeconds = $this->stuConfig->getResetSettings()->getDelayInSeconds();

        while ($sleepCountdownInSeconds > 0) {
            $io->info('  - sleeping for ' . $sleepCountdownInSeconds . ' seconds', true);
            sleep(5);

            $sleepCountdownInSeconds -= 5;
        }

        $this->entityManager->beginTransaction();

        try {
            $this->deleteAllStorages();
            $this->crewReset->deleteAllCrew();
            $this->fleetReset->deleteAllFleets();
            $this->knReset->resetKn();
            $this->pmReset->unsetAllInboxReferences();
            $this->pmReset->resetAllNonNpcPmFolders();
            $this->pmReset->resetPms();
            $this->pmReset->deleteAllContacts();

            $this->tradeReset->deleteAllBasicTrades();
            $this->tradeReset->deleteAllDeals();
            $this->tradeReset->deleteAllLotteryTickets();
            $this->tradeReset->deleteAllTradeShoutboxEntries();
            $this->tradeReset->deleteAllTradeTransactions();

            $this->allianceReset->deleteAllAllianceBoards();
            $this->allianceReset->deleteAllAllianceJobs();
            $this->allianceReset->deleteAllAllianceRelations();
            $this->allianceReset->deleteAllAlliances();

            $this->shipReset->deleteAllBuildplans();
            $this->shipReset->deleteAllTradeposts();
            $this->shipReset->deactivateAllTractorBeams();
            $this->shipReset->undockAllDockedShips();
            $this->shipReset->deleteAllShips();

            $this->userReset->deletePirateWrathEntries();
            $this->userReset->archiveBlockedUsers();
            $this->userReset->deleteAllDatabaseUserEntries();
            $this->userReset->deleteAllNotes();
            $this->userReset->deleteAllPrestigeLogs();
            $this->userReset->resetNpcs();
            $this->userReset->deleteAllUserInvitations();
            $this->userReset->deleteAllUserIpTableEntries();
            $this->userReset->deleteAllUserProfileVisitors();

            $this->mapReset->deleteAllUserMaps();
            $this->mapReset->deleteAllFlightSignatures();
            $this->mapReset->deleteAllAstroEntries();
            $this->mapReset->deleteAllColonyScans();
            $this->mapReset->deleteAllTachyonScans();
            $this->mapReset->deleteAllUserLayers();

            $io->info('  - deleting all non npc users', true);
            $this->playerDeletion->handleReset();
            $this->entityManager->flush();

            // clear game data
            $this->resetGameTurns($io);
            $this->gameRequestRepository->truncateAllGameRequests();

            //other
            $this->resetColonySurfaceMasks($io);
            $this->deleteHistory($io);
        } catch (Throwable $t) {
            $this->entityManager->rollback();

            throw $t;
        }

        $this->resetSequences($io);

        $this->entityManager->commit();

        $resetMs = hrtime(true) - $startTime;

        $io->info(sprintf('finished game reset, milliseconds: %d', (int) $resetMs / 1_000_000), true);
    }

    private function setGameState(int $stateId, Interactor $io): void
    {
        $this->gameConfigRepository->updateGameState($stateId, $this->connection);

        $io->info("  - setting game state to '" . GameEnum::gameStateTypeToDescription($stateId) . " - " . $stateId . "'", true);
    }

    private function deleteAllStorages(): void
    {
        $this->storageReset->deleteAllTradeOffers();
        $this->storageReset->deleteAllTorpedoStorages();
        $this->storageReset->deleteAllStorages();
    }

    /**
     * Resets the saved colony surface mask
     */
    private function resetColonySurfaceMasks(Interactor $io): void
    {
        $io->info('  - reset colony surfaces', true);

        foreach ($this->colonyRepository->findAll() as $colony) {
            $colony->setMask(null);

            $this->planetFieldRepository->truncateByColony($colony);

            $this->colonyRepository->save($colony);
        }
    }

    /**
     * Deletes all history entries
     */
    private function deleteHistory(Interactor $io): void
    {
        $io->info('  - deleting history', true);

        $this->historyRepository->truncateAllEntities();
    }

    /**
     * Deletes all existing game turns and starts over
     */
    private function resetGameTurns(Interactor $io): void
    {
        $io->info('  - reset game turns', true);

        $this->gameTurnRepository->truncateAllGameTurns();

        $turn = $this->gameTurnRepository->prototype()
            ->setTurn(1)
            ->setStart(time())
            ->setEnd(0);

        $this->gameTurnRepository->save($turn);
    }

    private function resetSequences(Interactor $io): void
    {
        $io->info('  - resetting sequences', true);

        $connection = $this->entityManager->getConnection();

        $result = $connection->executeQuery(
            "SELECT  'SELECT SETVAL(' ||quote_literal(S.relname)|| ',
                        (SELECT COALESCE(MAX(' ||quote_ident(C.attname)|| '), 1)
                        FROM ' ||quote_ident(T.relname)  || '));'
            FROM pg_class S
            JOIN pg_depend D
                ON S.oid = D.objid
            JOIN pg_class T
                ON D.refobjid = T.oid
            JOIN pg_attribute C
                ON D.refobjid = C.attrelid
                AND D.refobjsubid = C.attnum
            WHERE S.relkind = 'S'
            ORDER BY S.relname"
        );

        $count = 0;

        while ($query = $result->fetchOne()) {
            $connection->executeQuery(
                $query
            );

            $count++;
        }

        $connection->executeQuery(
            sprintf(
                "SELECT SETVAL('stu_user_id_seq', %d)",
                UserEnum::USER_FIRST_ID
            )
        );

        $io->info('    - resetted ' . $count . ' sequences', true);
    }
}
