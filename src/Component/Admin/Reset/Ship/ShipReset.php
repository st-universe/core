<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Ship;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShipReset implements ShipResetInterface
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private TradePostRepositoryInterface $tradePostRepository,
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[\Override]
    public function deactivateAllTractorBeams(): void
    {
        echo "  - deactivate all tractor beams\n";

        $this->entityManager->getConnection()->executeQuery('update stu_spacecraft set tractored_ship_id = null');

        $this->entityManager->flush();
    }

    #[\Override]
    public function undockAllDockedShips(): void
    {
        echo "  - undock all ships\n";

        $this->entityManager->getConnection()->executeQuery('update stu_ship set docked_to_id = null');

        $this->entityManager->flush();
    }

    #[\Override]
    public function deleteAllTradeposts(): void
    {
        echo "  - deleting all tradeposts\n";

        $this->tradePostRepository->truncateAllTradeposts();

        $this->entityManager->flush();
    }

    #[\Override]
    public function deleteAllShips(): void
    {
        echo "  - deleting all ships and stations\n";

        $this->spacecraftRepository->truncateAllSpacecrafts();

        $this->entityManager->flush();
    }

    #[\Override]
    public function deleteAllBuildplans(): void
    {
        echo "  - deleting all buildplans\n";

        $this->entityManager->getConnection()->executeQuery('update stu_spacecraft set plan_id = null');

        $this->spacecraftBuildplanRepository->truncateAllBuildplansExceptNoOne();

        $this->entityManager->flush();
    }
}
