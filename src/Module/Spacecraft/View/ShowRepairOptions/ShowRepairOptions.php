<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowRepairOptions;

use Override;
use request;
use Stu\Component\Spacecraft\Repair\RepairTaskConstants;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ShowRepairOptions implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_REPAIR_OPTIONS';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private RepairUtilInterface $repairUtil
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        $spacecraft = $wrapper->get();

        $game->setPageTitle("Reparatur Optionen");

        if (!$spacecraft->hasEnoughCrew()) {
            $game->addInformation("Nicht genügend Crew vorhanden");
            $game->setMacroInAjaxWindow('');
            return;
        }

        $game->setMacroInAjaxWindow('html/ship/repairoptions.twig');

        $repairOptions = $this->repairUtil->determineRepairOptions($wrapper);

        $game->setTemplateVar('WRAPPER', $wrapper);
        $game->setTemplateVar('REPAIR_OPTIONS', $repairOptions);
        $game->setTemplateVar('ENGINEER_COUNT', $this->repairUtil->determineFreeEngineerCount($spacecraft));
        $game->setTemplateVar('ROUNDS', $this->repairUtil->getRepairDuration($wrapper));

        $game->setTemplateVar('SPARE_PARTS_ONLY', (RepairTaskConstants::SPARE_PARTS_ONLY_MIN + RepairTaskConstants::SPARE_PARTS_ONLY_MAX) / 2);
        $game->setTemplateVar('SYSTEM_COMPONENTS_ONLY', (RepairTaskConstants::SYSTEM_COMPONENTS_ONLY_MIN + RepairTaskConstants::SYSTEM_COMPONENTS_ONLY_MAX) / 2);
        $game->setTemplateVar('BOTH', (RepairTaskConstants::BOTH_MIN + RepairTaskConstants::BOTH_MAX) / 2);
    }
}
