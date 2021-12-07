<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class DoManualProcessTick2 implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_PROCESS_TICK2';

    private EntityManagerInterface $entityManager;

    private ShipRepositoryInterface $shipRepository;

    private CrewCreatorInterface $crewCreator;

    public function __construct(
        EntityManagerInterface $entityManager,
        ShipRepositoryInterface $shipRepository,
        CrewCreatorInterface $crewCreator
    ) {
        $this->entityManager = $entityManager;
        $this->shipRepository = $shipRepository;
        $this->crewCreator = $crewCreator;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->getUser()->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $count = 0;

        $ships = $this->shipRepository->findAll();

        foreach ($ships as $ship) {

            if ($count === 5) {
                break;
            }

            $bp = $ship->getBuildplan();

            if ($bp === null) {
                continue;
            }

            if ($bp->getCrew() < 1) {
                continue;
            }

            if (
                $ship->getCrewCount() === 0
                && $ship->getSystemState(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)
            ) {
                $count++;

                $this->crewCreator->createShipCrew($ship);
            }
        }



        $this->entityManager->flush();

        $game->addInformationf("Crew wurde repariert! Schiffsanzahl: %d", $count);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
