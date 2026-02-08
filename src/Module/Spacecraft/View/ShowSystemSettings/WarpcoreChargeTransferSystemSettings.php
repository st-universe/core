<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSystemSettings;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class WarpcoreChargeTransferSystemSettings implements SystemSettingsProviderInterface
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}
    #[\Override]
    public function setTemplateVariables(
        SpacecraftSystemTypeEnum $systemType,
        SpacecraftWrapperInterface $wrapper,
        GameControllerInterface $game
    ): void {
        $spacecraft = $wrapper->get();

        if (!$spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::WARPCORE_CHARGE_TRANSFER)) {
            return;
        }

        if (
            $spacecraft->getSystemState(SpacecraftSystemTypeEnum::WARPDRIVE) ||
            $spacecraft->getSystemState(SpacecraftSystemTypeEnum::SHIELDS)
        ) {
            $game->setTemplateVar('SYSTEMWARNING', true);
            $game->setTemplateVar('WARNING_MESSAGE', 'Warpantrieb und Schilde müssen deaktiviert sein');
        } else {
            $nearbySpacecrafts = $this->spacecraftRepository->getNearbySpacecraftsForWarpcoreTransfer($spacecraft);
            $groups = $this->spacecraftWrapperFactory->wrapSpacecraftsAsGroups($nearbySpacecrafts);
            $game->setTemplateVar('SPACECRAFT_GROUPS', $groups);
        }

        $game->setMacroInAjaxWindow('html/spacecraft/warpcoreChargeTransfer.twig');
        $game->setTemplateVar('SPACECRAFT', $spacecraft);
    }
}
