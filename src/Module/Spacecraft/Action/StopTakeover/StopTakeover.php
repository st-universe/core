<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\StopTakeover;

use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class StopTakeover implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_STOP_TAKEOVER';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private ShipTakeoverManagerInterface $shipTakeoverManager
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $shipId = request::getIntFatal('id');

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        $ship = $wrapper->get();

        $takeover = $ship->getTakeoverActive();
        if ($takeover === null) {
            return;
        }

        $this->shipTakeoverManager->cancelTakeover($takeover);

        $game->addInformationf(
            'Übernahme der %s wurde abgebrochen',
            $takeover->getTargetSpacecraft()->getName()
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
