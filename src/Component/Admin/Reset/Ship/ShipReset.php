<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Ship;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShipReset implements ShipResetInterface
{
    private ShipRepositoryInterface $shipRepository;

    private TradePostRepositoryInterface $tradePostRepository;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        TradePostRepositoryInterface $tradePostRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ShipSystemManagerInterface $shipSystemManager,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->shipRepository = $shipRepository;
        $this->tradePostRepository = $tradePostRepository;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->entityManager = $entityManager;
    }

    public function deactivateAllTractorBeams(): void
    {
        echo "  - deactivate all tractor beams\n";

        foreach ($this->shipRepository->getAllTractoringShips() as $ship) {
            $this->shipSystemManager->deactivate(
                $this->shipWrapperFactory->wrapShip($ship),
                ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM,
                true
            );
        }

        $this->entityManager->flush();
    }

    public function undockAllDockedShips(): void
    {
        echo "  - undock all ships\n";

        foreach ($this->shipRepository->getAllDockedShips() as $ship) {
            $ship->setDockedTo(null);
            $ship->setDockedToId(null);
            $this->shipRepository->save($ship);
        }

        $this->entityManager->flush();
    }

    public function deleteAllTradeposts(): void
    {
        echo "  - deleting all tradeposts\n";

        foreach ($this->tradePostRepository->findAll() as $tradepost) {
            $ship = $tradepost->getShip();
            $ship->setTradePost(null);
            $this->shipRepository->save($ship);

            $this->tradePostRepository->delete($tradepost);
        }

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
