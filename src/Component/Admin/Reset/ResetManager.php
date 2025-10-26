<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset;

use Ahc\Cli\IO\Interactor;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Admin\Reset\Alliance\AllianceResetInterface;
use Stu\Component\Admin\Reset\Communication\PmResetInterface;
use Stu\Component\Admin\Reset\Fleet\FleetResetInterface;
use Stu\Component\Admin\Reset\Ship\ShipResetInterface;
use Stu\Component\Admin\Reset\User\UserResetInterface;
use Stu\Component\Game\GameStateEnum;
use Stu\Component\Player\Deletion\PlayerDeletionInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Throwable;

final class ResetManager implements ResetManagerInterface
{
    public function __construct(
        private readonly PmResetInterface $pmReset,
        private readonly AllianceResetInterface $allianceReset,
        private readonly FleetResetInterface $fleetReset,
        private readonly ShipResetInterface $shipReset,
        private readonly UserResetInterface $userReset,
        private readonly GameConfigRepositoryInterface $gameConfigRepository,
        private readonly PlayerDeletionInterface $playerDeletion,
        private readonly ColonyRepositoryInterface $colonyRepository,
        private readonly GameTurnRepositoryInterface $gameTurnRepository,
        private readonly PlanetFieldRepositoryInterface $planetFieldRepository,
        private readonly EntityReset $entityReset,
        private readonly SequenceResetInterface $sequenceReset,
        private readonly StuConfigInterface $stuConfig,
        private readonly EntityManagerInterface $entityManager,
        private readonly Connection $connection
    ) {}

    #[\Override]
    public function performReset(Interactor $io): void
    {
        $io->info('starting game reset', true);

        $startTime = hrtime(true);

        $this->setGameState(GameStateEnum::RESET, $io);

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
            // muss vorher
            $this->pmReset->unsetAllInboxReferences();
            $this->pmReset->resetAllNonNpcPmFolders();
            $this->allianceReset->unsetUserAlliances();
            $this->fleetReset->unsetShipFleetReference();

            //HERE
            $this->entityReset->reset($io);

            $this->shipReset->deleteAllBuildplans();
            $this->shipReset->deleteAllTradeposts();
            $this->shipReset->deactivateAllTractorBeams();
            $this->shipReset->undockAllDockedShips();
            $this->shipReset->deleteAllShips();

            $this->userReset->archiveBlockedUsers();
            $this->userReset->resetNpcs();

            $io->info('  - deleting all non npc users', true);
            $this->playerDeletion->handleReset();
            $this->entityManager->flush();

            $this->createFirstGameTurn($io);

            //other
            $this->resetColonySurfaceMasks($io);
        } catch (Throwable $t) {
            $this->entityManager->rollback();

            throw $t;
        }

        $this->resetSequences($io);

        $this->entityManager->commit();

        $resetMs = hrtime(true) - $startTime;

        $io->info(sprintf('finished game reset, milliseconds: %d', (int) $resetMs / 1_000_000), true);
    }

    private function setGameState(GameStateEnum $state, Interactor $io): void
    {
        $this->gameConfigRepository->updateGameState($state, $this->connection);

        $io->info("  - setting game state to '" . $state->getDescription() . " - " . $state->value . "'", true);
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

    private function createFirstGameTurn(Interactor $io): void
    {
        $io->info('  - create first game turn', true);

        $turn = $this->gameTurnRepository->prototype()
            ->setTurn(1)
            ->setStart(time())
            ->setEnd(0);

        $this->gameTurnRepository->save($turn);
    }

    private function resetSequences(Interactor $io): void
    {
        $io->info('  - resetting sequences', true);

        $count = $this->sequenceReset->resetSequences();

        $io->info('    - resetted ' . $count . ' sequences', true);
    }
}
