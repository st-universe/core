<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\DeactivateRPGModule;

use Override;
use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class DeactivateRPGModule implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEACTIVATE_RPG_MODULE';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private ActivatorDeactivatorHelperInterface $helper
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $this->helper->deactivate(request::indInt('id'), SpacecraftSystemTypeEnum::SYSTEM_RPG_MODULE, $game, true);

        $ship = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $ship->setDisabled(false);

        $this->spacecraftLoader->save($ship);

        $game->addInformation("Das RPG Modul wurde deaktiviert");
    }
    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
