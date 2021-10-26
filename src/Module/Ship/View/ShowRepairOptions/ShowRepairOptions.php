<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowRepairOptions;

use request;
use Stu\Component\Ship\RepairTaskEnum;
use Stu\Component\Ship\Selfrepair\SelfrepairUtilInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowRepairOptions implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_REPAIR_OPTIONS';

    private ShipLoaderInterface $shipLoader;

    private SelfrepairUtilInterface $selfrepairUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        SelfrepairUtilInterface $selfrepairUtil
    ) {
        $this->shipLoader = $shipLoader;
        $this->selfrepairUtil = $selfrepairUtil;
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

        $repairOptions = $this->selfrepairUtil->determineRepairOptions($ship);

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('REPAIR_OPTIONS', $repairOptions);
        $game->setTemplateVar('ENGINEER_COUNT', $this->selfrepairUtil->determineFreeEngineerCount($ship));
        $game->setTemplateVar('ERROR', false);

        $game->setTemplateVar('SPARE_PARTS_ONLY', (int)((RepairTaskEnum::SPARE_PARTS_ONLY_MIN + RepairTaskEnum::SPARE_PARTS_ONLY_MAX) / 2));
        $game->setTemplateVar('SYSTEM_COMPONENTS_ONLY', (int)((RepairTaskEnum::SYSTEM_COMPONENTS_ONLY_MIN + RepairTaskEnum::SYSTEM_COMPONENTS_ONLY_MAX) / 2));
        $game->setTemplateVar('BOTH', (int)((RepairTaskEnum::BOTH_MIN + RepairTaskEnum::BOTH_MAX) / 2));
    }
}
