<?php

namespace Stu\Module\Ship\Lib\Movement;

use Override;
use InvalidArgumentException;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Message\MessageFactoryInterface;
use Stu\Module\Ship\Lib\Movement\Route\RouteModeEnum;
use Stu\Orm\Entity\ShipInterface;

//TODO unit tests
final class ShipMovementInformationAdder implements ShipMovementInformationAdderInterface
{
    public function __construct(private MessageFactoryInterface $messageFactory)
    {
    }

    #[Override]
    public function reachedDestination(
        ShipInterface $ship,
        bool $isFleetMode,
        RouteModeEnum $routeMode,
        MessageCollectionInterface $messages
    ): void {

        $name = $isFleetMode ? 'Flotte' : $ship->getName();
        $routeModeValue = $routeMode->value;

        $message = $this->messageFactory->createMessage();
        $messages->add($message);

        switch ($routeMode) {
            case RouteModeEnum::ROUTE_MODE_FLIGHT:
                $message->add(sprintf(
                    _('Die %s fliegt in Sektor %s ein'),
                    $name,
                    $ship->getSectorString()
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY:
                $system = $ship->getSystem();

                if ($system === null) {
                    throw new InvalidArgumentException(sprintf('route mode %d does not exist', $routeModeValue));
                }

                $message->add(sprintf(
                    _('Die %s fliegt in das %s-System ein'),
                    $name,
                    $system->getName()
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT:
                $message->add(sprintf(
                    _('Die %s hat das Sternsystem verlassen'),
                    $name,
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY:
                $system = $ship->getSystem();

                if ($system === null) {
                    throw new InvalidArgumentException(sprintf('route mode %d does not exist', $routeModeValue));
                }

                $message->add(sprintf(
                    _('Die %s fliegt in das %s ein'),
                    $name,
                    $system->getName()
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_EXIT:
                $message->add($isFleetMode ? 'Die Flotte hat das Wurmloch verlassen' : 'Das Wurmloch wurde verlassen');
                break;
            case RouteModeEnum::ROUTE_MODE_TRANSWARP:
                $message->add(sprintf(
                    _('Die %s verlässt den Transwarpkanal in Sektor %s'),
                    $name,
                    $ship->getSectorString()
                ));
                break;
            default:
                throw new InvalidArgumentException(sprintf('route mode %d does not exist', $routeMode));
        }
    }

    #[Override]
    public function reachedDestinationDestroyed(
        ShipInterface $ship,
        string $leadShipName,
        bool $isFleetMode,
        RouteModeEnum $routeMode,
        MessageCollectionInterface $messages
    ): void {

        $name = $isFleetMode ? 'gesamte Flotte' : $leadShipName;
        $routeModeValue = $routeMode->value;

        $message = $this->messageFactory->createMessage();
        $messages->add($message);

        switch ($routeMode) {
            case RouteModeEnum::ROUTE_MODE_FLIGHT:
                $message->add(sprintf(
                    _('Beim Einflug in Sektor %s wurde die %s zerstört'),
                    $ship->getSectorString(),
                    $name
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY:
                $system = $ship->getSystem();

                if ($system === null) {
                    throw new InvalidArgumentException(sprintf('route mode %d does not exist', $routeModeValue));
                }

                $message->add(sprintf(
                    _('Beim Einflug in das %s-System wurde die %s zerstört'),
                    $system->getName(),
                    $name
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT:
                $message->add(sprintf(
                    _('Beim Verlassen des Sternsystem wurde die %s zerstört'),
                    $name
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY:
                $system = $ship->getSystem();

                if ($system === null) {
                    throw new InvalidArgumentException(sprintf('route mode %d does not exist', $routeModeValue));
                }
                $message->add(sprintf(
                    _('Beim Einflug in das %s wurde die %s zerstört'),
                    $system->getName(),
                    $name
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_EXIT:
                $message->add(
                    sprintf(
                        _('Beim Verlassen des Wurmlochs wurde die %s zerstört'),
                        $name
                    )
                );
                break;
            case RouteModeEnum::ROUTE_MODE_TRANSWARP:
                $message->add(
                    sprintf(
                        _('Beim Verlassen des Transwarpkanals wurde die %s zerstört'),
                        $name
                    )
                );
                break;
            default:
                throw new InvalidArgumentException(sprintf('route mode %d does not exist', $routeModeValue));
        }
    }

    #[Override]
    public function pulledTractoredShip(
        ShipInterface $ship,
        ShipInterface $tractoredShip,
        RouteModeEnum $routeMode,
        MessageCollectionInterface $messages
    ): void {
        $tractoredShipName = $tractoredShip->getName();

        $message = $this->messageFactory->createMessage(
            $ship->getUser()->getId(),
            $tractoredShip->getUser()->getId()
        );
        $messages->add($message);

        $sectorString = $ship->getSectorString();

        switch ($routeMode) {
            case RouteModeEnum::ROUTE_MODE_FLIGHT:
                $message->add(sprintf(
                    _('Die %s wurde via Traktorstrahl bis %s mitgezogen'),
                    $tractoredShipName,
                    $sectorString
                ));
                break;
            case RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY:
                $message->add(sprintf(_('Die %s wurde mit in das System gezogen'), $tractoredShipName));
                break;
            case RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT:
                $message->add(sprintf(_('Die %s wurde mit aus dem System gezogen'), $tractoredShipName));
                break;
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY:
                $message->add(sprintf(_('Die %s wurde mit in das Wurmloch gezogen'), $tractoredShipName));
                break;
            case RouteModeEnum::ROUTE_MODE_WORMHOLE_EXIT:
                $message->add(sprintf(_('Die %s wurde mit aus dem Wurmloch gezogen'), $tractoredShipName));
                break;
            default:
                throw new InvalidArgumentException(sprintf('route mode %d does not exist', $routeMode->value));
        }
    }
}
