<?php

namespace Stu\Module\Ship\Lib\Movement;

use InvalidArgumentException;
use Stu\Lib\InformationWrapper;
use Stu\Module\Ship\Lib\Movement\Route\RouteModeEnum;
use Stu\Orm\Entity\ShipInterface;

//TODO unit tests
final class ShipMovementInformationAdder implements ShipMovementInformationAdderInterface
{
    public function reachedDestination(
        ShipInterface $ship,
        bool $isFleetMode,
        int $routeMode,
        InformationWrapper $informations
    ): void {

        switch ($routeMode) {
            case RouteModeEnum::ROUTE_MODE_FLIGHT:
                $informations->addInformation(sprintf(
                    _('Die %s fliegt in Sektor %s ein'),
                    $isFleetMode ? 'Flotte' : $ship->getName(),
                    $ship->getSectorString()
                ));
            case RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY:
                $informations->addInformation(sprintf(
                    _('Die %s fliegt in das %s-System ein'),
                    $isFleetMode ? 'Flotte' : $ship->getName(),
                    $ship->getSystem()->getName()
                ));
            case RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT:
                $informations->addInformation(sprintf(
                    _('Die %s hat das Sternsystem verlassen'),
                    $isFleetMode ? 'Flotte' : $ship->getName(),
                ));
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY:
                $informations->addInformation(sprintf(
                    _('Die %s fliegt in das %s ein'),
                    $isFleetMode ? 'Flotte' : $ship->getName(),
                    $ship->getSystem()->getName()
                ));
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_EXIT:
                $informations->addInformation($isFleetMode ? 'Die Flotte hat das Wurmloch verlassen' : 'Das Wurmloch wurde verlassen');
            default:
                throw new InvalidArgumentException(sprintf('route mode %d does not exist', $routeMode));
        }
    }

    public function pulledTractoredShip(
        String $tractoredShipName,
        int $routeMode,
        InformationWrapper $informations
    ): void {
        switch ($routeMode) {
            case RouteModeEnum::ROUTE_MODE_FLIGHT:
                $informations->addInformation(sprintf(_('Die %s wurde per Traktorstrahl mitgezogen'), $tractoredShipName));
            case RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY:
                $informations->addInformation(sprintf(_('Die %s wurde mit in das System gezogen'), $tractoredShipName));
            case RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT:
                $informations->addInformation(sprintf(_('Die %s wurde mit aus dem System gezogen'), $tractoredShipName));
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY:
                $informations->addInformation(sprintf(_('Die %s wurde mit in das Wurmloch gezogen'), $tractoredShipName));
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_EXIT:
                $informations->addInformation(sprintf(_('Die %s wurde mit aus dem Wurmloch gezogen'), $tractoredShipName));
            default:
                throw new InvalidArgumentException(sprintf('route mode %d does not exist', $routeMode));
        }
    }

    public function notEnoughEnergyforTractoring(
        ShipInterface $ship,
        int $routeMode,
        InformationWrapper $informations
    ): void {

        $tractoredShip = $ship->getTractoredShip();
        if ($tractoredShip === null) {
            throw new InvalidArgumentException('this should not happen');
        }

        switch ($routeMode) {
            case RouteModeEnum::ROUTE_MODE_FLIGHT:
                $informations->addInformation(sprintf(
                    _('Der Traktorstrahl auf die %s wurde in Sektor %d|%d aufgrund Energiemangels deaktiviert'),
                    $tractoredShip->getName(),
                    $ship->getPosX(),
                    $ship->getPosY()
                ));
            case RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY:
                $informations->addInformation(sprintf(
                    _('Der Traktorstrahl auf die %s wurde beim Systemeinflug aufgrund Energiemangels deaktiviert'),
                    $tractoredShip->getName()
                ));
            case RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT:
                $informations->addInformation(sprintf(
                    _('Der Traktorstrahl auf die %s wurde beim Verlassen des Systems aufgrund Energiemangels deaktiviert'),
                    $tractoredShip->getName()
                ));
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY:
                $informations->addInformation(sprintf(
                    _('Der Traktorstrahl auf die %s wurde beim Wurmlocheinflug aufgrund Energiemangels deaktiviert'),
                    $tractoredShip->getName()
                ));
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_EXIT:
                $informations->addInformation(sprintf(
                    _('Der Traktorstrahl auf die %s wurde beim Verlassen des Wurmlochs aufgrund Energiemangels deaktiviert'),
                    $tractoredShip->getName()
                ));
            default:
                throw new InvalidArgumentException(sprintf('route mode %d does not exist', $routeMode));
        }
    }
}
