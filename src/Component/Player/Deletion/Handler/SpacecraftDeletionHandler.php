<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ConstructionProgressRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\MiningQueueRepositoryInterface;

final class SpacecraftDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private ConstructionProgressRepositoryInterface $constructionProgressRepository,
        private SpacecraftRemoverInterface $spacecraftRemover,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private ShipUndockingInterface $shipUndocking,
        private EntityManagerInterface $entityManager,
        private MiningQueueRepositoryInterface $miningQueueRepository
    ) {}

    #[Override]
    public function delete(UserInterface $user): void
    {
        foreach ($this->spacecraftRepository->getByUser($user) as $spacecraft) {

            // do nothing if tradepost, because it gets handled in TradepostDeletionHandler
            if (
                $spacecraft instanceof StationInterface
                && $spacecraft->getTradePost() !== null
            ) {
                continue;
            }

            if ($spacecraft instanceof StationInterface) {
                $this->deleteConstructionProgress($spacecraft);
                $this->undockAllDockedShips($spacecraft);
            }

            $this->unsetTractor($spacecraft);
            if ($spacecraft instanceof ShipInterface) {
                $this->deleteMiningQueue($spacecraft);
            }
            $this->spacecraftRemover->remove($spacecraft, true);
        }
    }

    private function deleteConstructionProgress(StationInterface $station): void
    {
        $progress = $station->getConstructionProgress();
        if ($progress !== null) {
            $this->constructionProgressRepository->delete($progress);
            $station->resetConstructionProgress();
        }
    }

    private function undockAllDockedShips(StationInterface $station): void
    {
        $anyDocked = $this->shipUndocking->undockAllDocked($station);

        if ($anyDocked) {
            $this->entityManager->flush();
        }
    }

    private function unsetTractor(SpacecraftInterface $spacecraft): void
    {
        $tractoredShip = $spacecraft->getTractoredShip();

        if ($tractoredShip === null) {
            return;
        }

        $this->spacecraftSystemManager->deactivate(
            $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft),
            SpacecraftSystemTypeEnum::TRACTOR_BEAM,
            true
        );
    }

    private function deleteMiningQueue(ShipInterface $spacecraft): void
    {
        $miningqueue = $spacecraft->getMiningQueue();
        if ($miningqueue !== null) {
            $this->miningQueueRepository->delete($miningqueue);
        }
    }
}
