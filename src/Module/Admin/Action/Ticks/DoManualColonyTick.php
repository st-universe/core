<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use request;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tick\Colony\ColonyTickInterface;
use Stu\Module\Tick\Colony\ColonyTickManagerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class DoManualColonyTick implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_COLONY_TICK';

    private ColonyTickManagerInterface $colonyTickManager;

    private ColonyTickInterface $colonyTick;

    private ColonyRepositoryInterface $colonyRepository;

    private CommodityRepositoryInterface $commodityRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ColonyTickManagerInterface $colonyTickManager,
        ColonyTickInterface $colonyTick,
        ColonyRepositoryInterface $colonyRepository,
        CommodityRepositoryInterface $commodityRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->colonyTickManager = $colonyTickManager;
        $this->colonyTick = $colonyTick;
        $this->colonyRepository = $colonyRepository;
        $this->commodityRepository = $commodityRepository;
        $this->entityManager = $entityManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        //check if single or all colonies
        if (!request::getVarByMethod(request::postvars(), 'colonytickid')) {
            $this->colonyTickManager->work(1);
            $game->addInformation("Der Kolonie-Tick für alle Kolonien wurde durchgeführt!");
        } else {
            $commodityArray = $this->commodityRepository->getAll();

            $colonyId = request::postInt('colonytickid');
            $colony = $this->colonyRepository->find($colonyId);

            $this->colonyTick->work($colony, $commodityArray);
            $this->entityManager->flush();

            $game->addInformation("Der Kolonie-Tick für diese Kolonie wurde durchgeführt!");
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
