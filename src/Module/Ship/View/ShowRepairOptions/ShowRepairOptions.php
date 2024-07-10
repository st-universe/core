<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowRepairOptions;

use Override;
use request;
use Stu\Component\Ship\Repair\RepairUtilInterface;
use Stu\Component\Ship\RepairTaskEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowRepairOptions implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_REPAIR_OPTIONS';

    public function __construct(private ShipLoaderInterface $shipLoader, private RepairUtilInterface $repairUtil)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        $ship = $wrapper->get();

        $game->setPageTitle("Reparatur Optionen");
        $game->setMacroInAjaxWindow('html/ship/repairoptions.twig');

        $game->setTemplateVar('ERROR', true);

        if (!$ship->hasEnoughCrew()) {
            $game->addInformation("Nicht genügend Crew vorhanden");
            return;
        }

        $repairOptions = $this->repairUtil->determineRepairOptions($wrapper);

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('REPAIR_OPTIONS', $repairOptions);
        $game->setTemplateVar('ENGINEER_COUNT', $this->repairUtil->determineFreeEngineerCount($ship));
        $game->setTemplateVar('ERROR', false);
        $game->setTemplateVar('ROUNDS', $this->repairUtil->getRepairDuration($wrapper));

        $game->setTemplateVar('SPARE_PARTS_ONLY', (RepairTaskEnum::SPARE_PARTS_ONLY_MIN + RepairTaskEnum::SPARE_PARTS_ONLY_MAX) / 2);
        $game->setTemplateVar('SYSTEM_COMPONENTS_ONLY', (RepairTaskEnum::SYSTEM_COMPONENTS_ONLY_MIN + RepairTaskEnum::SYSTEM_COMPONENTS_ONLY_MAX) / 2);
        $game->setTemplateVar('BOTH', (RepairTaskEnum::BOTH_MIN + RepairTaskEnum::BOTH_MAX) / 2);
    }
}
