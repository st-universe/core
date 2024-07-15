<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Transwarp;

use Override;
use request;
use RuntimeException;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Ship\Action\MoveShip\AbstractDirectedMovement;
use Stu\Module\Ship\Action\MoveShip\MoveShipRequestInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\Movement\Route\RandomSystemEntryInterface;
use Stu\Module\Ship\Lib\Movement\ShipMoverInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\UserLayerInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class Transwarp extends AbstractDirectedMovement
{
    public const string ACTION_IDENTIFIER = 'B_TRANSWARP';

    private MapInterface $destination;

    public function __construct(
        MoveShipRequestInterface $moveShipRequest,
        ShipLoaderInterface $shipLoader,
        ShipMoverInterface $shipMover,
        FlightRouteFactoryInterface $flightRouteFactory,
        RandomSystemEntryInterface $randomSystemEntry,
        DistributedMessageSenderInterface $distributedMessageSender,
        private MapRepositoryInterface $mapRepository
    ) {
        parent::__construct(
            $moveShipRequest,
            $shipLoader,
            $shipMover,
            $flightRouteFactory,
            $randomSystemEntry,
            $distributedMessageSender
        );
    }

    #[Override]
    protected function isSanityCheckFaultyConcrete(ShipWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        $layerId = request::postIntFatal('transwarplayer');

        //sanity check if user knows layer
        /** @var null|UserLayerInterface */
        $userLayer = $game->getUser()->getUserLayers()->get($layerId);
        if ($userLayer === null) {
            return true;
        }

        if ($wrapper->get()->getTranswarpCooldown() !== null) {
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

        if ($ship->getFleet() !== null) {
            $game->addInformation(_('Transwarpflug nicht möglich wenn Teil einer Flotte'));
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
    protected function getFlightRoute(ShipWrapperInterface $wrapper): FlightRouteInterface
    {
        return $this->flightRouteFactory->getRouteForMapDestination($this->destination, true);
    }
}
