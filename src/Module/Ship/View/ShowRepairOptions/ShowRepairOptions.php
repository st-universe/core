<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowRepairOptions;

use request;
use Stu\Component\Crew\CrewEnum;
use Stu\Component\Ship\RepairTaskEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

final class ShowRepairOptions implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_REPAIR_OPTIONS';

    private ShipLoaderInterface $shipLoader;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipSystemRepositoryInterface $shipSystemRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipSystemRepository = $shipSystemRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $game->setPageTitle("Reparatur Optionen");
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/shipmacros.xhtml/repairoptions');

        $game->setTemplateVar('ERROR', true);

        if (!$ship->hasEnoughCrew()) {
            $game->addInformation("Nicht genügend Crew vorhanden");
            return;
        }

        $repairOptions = [];

        //check for hull option
        $hullPercentage = (int) $ship->getHuell() * 100 / $ship->getMaxHuell();
        if ($hullPercentage < RepairTaskEnum::BOTH_MAX) {
            $hullSystem = $this->shipSystemRepository->prototype();
            $hullSystem->setSystemType(ShipSystemTypeEnum::SYSTEM_HULL);

            $repairOptions[] = $hullSystem;
        }

        //check for system options
        foreach ($ship->getDamagedSystems() as $system) {
            if ($system->getStatus() < RepairTaskEnum::BOTH_MAX) {
                $repairOptions[] = $system;
            }
        }

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('REPAIR_OPTIONS', $repairOptions);
        $game->setTemplateVar('ENGINEER_OPTIONS', $this->fetchFreeEngineers($ship));
        $game->setTemplateVar('ERROR', false);
    }

    private function fetchFreeEngineers(ShipInterface $ship): array
    {
        $engineerOptions = [];
        $nextNumber = 1;
        foreach ($ship->getCrewlist() as $shipCrew) {
            if (
                $shipCrew->getSlot() === CrewEnum::CREW_TYPE_TECHNICAL
                && $shipCrew->getRepairTask() === null
            ) {
                $engineerOptions[] = $nextNumber;
                $nextNumber++;
            }
        }

        return $engineerOptions;
    }
}
