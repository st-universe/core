<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\StarSystemMap;

final class BorgSphereFlight
{
    public function __construct(
        private ShipMoverInterface $mover,
        private FlightRouteFactoryInterface $routes,
        private SpacecraftWrapperFactoryInterface $wrappers,
        private SpacecraftSystemManagerInterface $systems,
        private DistributedMessageSenderInterface $messages,
        private ?BorgCompanionSupply $companions = null
    ) {}

    public function move(Ship $ship, Map|StarSystemMap $destination): void
    {
        if ($ship->getCondition()->isDisabled()) {
            return;
        }
        $wrapper = $this->wrappers->wrapShip($ship);
        $route = $ship->getLocation()::class === $destination::class
            ? $this->routes->getRouteForCoordinateDestination($ship, $destination->getX(), $destination->getY())
            : $this->routes->getRouteForMapDestination($destination);
        try {
            if ($route->hasSpecialDamageOnField()) {
                return;
            }
            $messages = $this->mover->checkAndMove($wrapper, $route);
            $this->messages->distributeMessageCollection($messages, $ship->getUser()->getId(), PrivateMessageFolderTypeEnum::SPECIAL_SHIP);
        } finally {
            if (!$ship->getCondition()->isDestroyed() && $ship->getWarpDriveState()) {
                $this->systems->deactivate($wrapper, SpacecraftSystemTypeEnum::WARPDRIVE, true);
            }
            $this->companions?->leaveWarp($ship);
        }
    }
}
