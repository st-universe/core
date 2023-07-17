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

        $name = $isFleetMode ? 'Flotte' : $ship->getName();

        switch ($routeMode) {
            case RouteModeEnum::ROUTE_MODE_FLIGHT:
                $informations->addInformation(sprintf(
                    _('Die %s fliegt in Sektor %s ein'),
                    $name,
                    $ship->getSectorString()
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY:
                $system = $ship->getSystem();

                if ($system === null) {
                    throw new InvalidArgumentException(sprintf('route mode %d does not exist', $routeMode));
                }

                $informations->addInformation(sprintf(
                    _('Die %s fliegt in das %s-System ein'),
                    $name,
                    $system->getName()
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT:
                $informations->addInformation(sprintf(
                    _('Die %s hat das Sternsystem verlassen'),
                    $name,
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY:
                $system = $ship->getSystem();

                if ($system === null) {
                    throw new InvalidArgumentException(sprintf('route mode %d does not exist', $routeMode));
                }

                $informations->addInformation(sprintf(
                    _('Die %s fliegt in das %s ein'),
                    $name,
                    $system->getName()
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_EXIT:
                $informations->addInformation($isFleetMode ? 'Die Flotte hat das Wurmloch verlassen' : 'Das Wurmloch wurde verlassen');
                break;
            default:
                throw new InvalidArgumentException(sprintf('route mode %d does not exist', $routeMode));
        }
    }

    public function reachedDestinationDestroyed(
        ShipInterface $ship,
        bool $isFleetMode,
        int $routeMode,
        InformationWrapper $informations
    ): void {

        $name = $isFleetMode ? 'gesamte Flotte' : $ship->getName();

        switch ($routeMode) {
            case RouteModeEnum::ROUTE_MODE_FLIGHT:
                $informations->addInformation(sprintf(
                    _('Beim Einflug in Sektor %s wurde die %s zerstört'),
                    $ship->getSectorString(),
                    $name
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY:
                $system = $ship->getSystem();

                if ($system === null) {
                    throw new InvalidArgumentException(sprintf('route mode %d does not exist', $routeMode));
                }

                $informations->addInformation(sprintf(
                    _('Beim Einflug in das %s-System wurde die %s zerstört'),
                    $system->getName(),
                    $name
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT:
                $informations->addInformation(sprintf(
                    _('Beim Verlassen des Sternsystem wurde die %s zerstört'),
                    $name
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY:
                $system = $ship->getSystem();

                if ($system === null) {
                    throw new InvalidArgumentException(sprintf('route mode %d does not exist', $routeMode));
                }
                $informations->addInformation(sprintf(
                    _('Beim Einflug in das %s wurde die %s zerstört'),
                    $system->getName(),
                    $name
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_EXIT:
                $informations->addInformation(
                    sprintf(
                        _('Beim Verlassen des Wurmlochs wurde die %s zerstört'),
                        $name
                    )
                );
                break;
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
                break;
            case RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY:
                $informations->addInformation(sprintf(_('Die %s wurde mit in das System gezogen'), $tractoredShipName));
                break;
            case RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT:
                $informations->addInformation(sprintf(_('Die %s wurde mit aus dem System gezogen'), $tractoredShipName));
                break;
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY:
                $informations->addInformation(sprintf(_('Die %s wurde mit in das Wurmloch gezogen'), $tractoredShipName));
                break;
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_EXIT:
                $informations->addInformation(sprintf(_('Die %s wurde mit aus dem Wurmloch gezogen'), $tractoredShipName));
                break;
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

        $tractoredShipName = $tractoredShip->getName();

        switch ($routeMode) {
            case RouteModeEnum::ROUTE_MODE_FLIGHT:
                $informations->addInformation(sprintf(
                    _('Der Traktorstrahl auf die %s wurde in Sektor %d|%d aufgrund Energiemangels deaktiviert'),
                    $tractoredShipName,
                    $ship->getPosX(),
                    $ship->getPosY()
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY:
                $informations->addInformation(sprintf(
                    _('Der Traktorstrahl auf die %s wurde beim Systemeinflug aufgrund Energiemangels deaktiviert'),
                    $tractoredShipName
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT:
                $informations->addInformation(sprintf(
                    _('Der Traktorstrahl auf die %s wurde beim Verlassen des Systems aufgrund Energiemangels deaktiviert'),
                    $tractoredShipName
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY:
                $informations->addInformation(sprintf(
                    _('Der Traktorstrahl auf die %s wurde beim Wurmlocheinflug aufgrund Energiemangels deaktiviert'),
                    $tractoredShipName
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_EXIT:
                $informations->addInformation(sprintf(
                    _('Der Traktorstrahl auf die %s wurde beim Verlassen des Wurmlochs aufgrund Energiemangels deaktiviert'),
                    $tractoredShipName
                ));
                break;
            default:
                throw new InvalidArgumentException(sprintf('route mode %d does not exist', $routeMode));
        }
    }
}
