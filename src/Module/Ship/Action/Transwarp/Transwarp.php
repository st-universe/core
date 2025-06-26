<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Transwarp;

use Override;
use request;
use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\RandomSystemEntryInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMoverInterface;
use Stu\Module\Spacecraft\Action\MoveShip\AbstractDirectedMovement;
use Stu\Module\Spacecraft\Action\MoveShip\MoveShipRequestInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\UserLayer;
use Stu\Orm\Repository\MapRepositoryInterface;

final class Transwarp extends AbstractDirectedMovement
{
    public const string ACTION_IDENTIFIER = 'B_TRANSWARP';

    private Map $destination;

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        MoveShipRequestInterface $moveShipRequest,
        SpacecraftLoaderInterface $spacecraftLoader,
        ShipMoverInterface $shipMover,
        FlightRouteFactoryInterface $flightRouteFactory,
        RandomSystemEntryInterface $randomSystemEntry,
        DistributedMessageSenderInterface $distributedMessageSender,
        private MapRepositoryInterface $mapRepository
    ) {
        parent::__construct(
            $moveShipRequest,
            $spacecraftLoader,
            $shipMover,
            $flightRouteFactory,
            $randomSystemEntry,
            $distributedMessageSender
        );
    }

    #[Override]
    protected function isSanityCheckFaultyConcrete(SpacecraftWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        $layerId = request::postIntFatal('transwarplayer');

        //sanity check if user knows layer
        /** @var null|UserLayer */
        $userLayer = $game->getUser()->getUserLayers()->get($layerId);
        if ($userLayer === null) {
            return true;
        }

        if ($wrapper->get()->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRANSWARP_COIL)->getCooldown() !== null) {
            return true;
        }

        // target check
        $cx = request::postInt('transwarpcx');
        $cy = request::postInt('transwarpcy');

        if (!$cx || !$cy) {
            $game->addInformation(_('Zielkoordinaten müssen angegeben werden'));
            return true;
        }

        $ship = $wrapper->get();

        if ($ship->getSystem() !== null) {
            $game->addInformation(_('Transwarp kann nur außerhalb von Systemen genutzt werden'));
            return true;
        }

        if (!$ship->getWarpDriveState()) {
            $game->addInformation(_('Der Warpantrieb muss aktiviert sein'));
            return true;
        }

        if ($ship->isTractoring()) {
            $game->addInformation(_('Transwarpflug nicht möglich bei aktiviertem Traktorstrahl'));
            return true;
        }

        if (
            $ship instanceof Ship
            && $ship->getFleet() !== null
            && $ship->getFleet()->getShipCount() > 1
        ) {
            $game->addInformation('Transwarpflug nicht möglich wenn Teil einer Flotte');
            return true;
        }

        $map = $this->mapRepository->getByCoordinates($userLayer->getLayer(), $cx, $cy);
        if ($map === null) {
            $game->addInformation(_('Zielkoordinaten existieren nicht'));
            return true;
        }

        $layer = $map->getLayer();
        if ($layer === null) {
            throw new RuntimeException('this should not happen');
        }

        if ($layer->isHidden()) {
            throw new SanityCheckException('tried to access hidden layer');
        }

        if (!$map->getFieldType()->getPassable()) {
            $game->addInformation(_('Zielkoordinaten können nicht angeflogen werden'));
            return true;
        }

        $this->destination = $map;

        return false;
    }

    #[Override]
    protected function getFlightRoute(SpacecraftWrapperInterface $wrapper): FlightRouteInterface
    {
        return $this->flightRouteFactory->getRouteForMapDestination($this->destination, true);
    }
}
