<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowWarpcoreChargeTransfer;

use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class ShowWarpcoreChargeTransfer implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_WARPCORE_CHARGE_TRANSFER';

    /**
     * @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader
     */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );

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

        $game->setPageTitle('Warpkern Ladungstransfer');
        $game->setMacroInAjaxWindow('html/spacecraft/warpcoreChargeTransfer.twig');
        $game->setTemplateVar('SPACECRAFT', $wrapper);
    }
}
