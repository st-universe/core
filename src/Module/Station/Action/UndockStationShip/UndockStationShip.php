<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\UndockStationShip;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;

final class UndockStationShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_UNDOCK_SHIP';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private ShipUndockingInterface $shipUndocking
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $wrappers = $this->spacecraftLoader->getWrappersBySourceAndUserAndTarget(
            request::indInt('id'),
            $game->getUser()->getId(),
            request::indInt('target')
        );

        $wrapper = $wrappers->getSource();
        $station = $wrapper->get();

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        if (
            !$target instanceof Ship
            || !$station instanceof Station
            || $target->getDockedTo() !== $station
        ) {
            return;
        }

        $this->shipUndocking->undockShip($station, $target);

        $game->getInfo()->addInformationf('Die %s wurde erfolgreich abgedockt', $target->getName());
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
