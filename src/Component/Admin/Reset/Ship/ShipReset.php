<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Ship;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShipReset implements ShipResetInterface
{
    private ShipRepositoryInterface $shipRepository;

    private TradePostRepositoryInterface $tradePostRepository;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        TradePostRepositoryInterface $tradePostRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->shipRepository = $shipRepository;
        $this->tradePostRepository = $tradePostRepository;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->entityManager = $entityManager;
    }

    public function deactivateAllTractorBeams(): void
    {
        echo "  - deactivate all tractor beams\n";

        $this->entityManager->getConnection()->executeQuery('update stu_ships set tractored_ship_id = null');

        $this->entityManager->flush();
    }

    public function undockAllDockedShips(): void
    {
        echo "  - undock all ships\n";

        $this->entityManager->getConnection()->executeQuery('update stu_ships set dock = null');

        $this->entityManager->flush();
    }

    public function deleteAllTradeposts(): void
    {
        echo "  - deleting all tradeposts\n";

        $this->tradePostRepository->truncateAllTradeposts();

        $this->entityManager->flush();
    }

    public function deleteAllShips(): void
    {
        echo "  - deleting all ships and stations\n";

        $this->shipRepository->truncateAllShips();

        $this->entityManager->flush();
    }

    public function deleteAllBuildplans(): void
    {
        echo "  - deleting all buildplans\n";

        $this->entityManager->getConnection()->executeQuery('update stu_ships set plans_id = null');

        $this->shipBuildplanRepository->truncateAllBuildplansExceptNoOne();

        $this->entityManager->flush();
    }
}
