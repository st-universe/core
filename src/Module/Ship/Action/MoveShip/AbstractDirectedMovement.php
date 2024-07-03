<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use Override;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\Movement\Route\RandomSystemEntryInterface;
use Stu\Module\Ship\Lib\Movement\ShipMoverInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

abstract class AbstractDirectedMovement implements ActionControllerInterface
{
    public function __construct(protected MoveShipRequestInterface $moveShipRequest, private ShipLoaderInterface $shipLoader, private ShipMoverInterface $shipMover, protected FlightRouteFactoryInterface $flightRouteFactory, protected RandomSystemEntryInterface $randomSystemEntry, private DistributedMessageSenderInterface $distributedMessageSender)
    {
    }

    abstract protected function isSanityCheckFaultyConcrete(ShipWrapperInterface $wrapper, GameControllerInterface $game): bool;

    abstract protected function getFlightRoute(ShipWrapperInterface $wrapper): FlightRouteInterface;

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $this->moveShipRequest->getShipId(),
            $userId
        );

        if ($this->isSanityCheckFaulty($wrapper, $game)) {
            return;
        }

        $ship = $wrapper->get();

        $messages = $this->shipMover->checkAndMove(
            $wrapper,
            $this->getFlightRoute($wrapper)
        );
        $game->addInformationWrapper($messages->getInformationDump());


        $this->distributedMessageSender->distributeMessageCollection(
            $messages,
            $userId,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );


        if ($ship->isDestroyed()) {
            $game->setView(ModuleViewEnum::SHIP);
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }

    private function isSanityCheckFaulty(ShipWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        $ship = $wrapper->get();

        if (!$ship->hasEnoughCrew($game)) {
            return true;
        }

        if ($ship->isTractored()) {
            $game->addInformation(_('Das Schiff wird von einem Traktorstrahl gehalten'));
            return true;
        }

        if ($ship->getHoldingWeb() !== null && $ship->getHoldingWeb()->isFinished()) {
            $game->addInformation(_('Das Schiff ist in einem Energienetz gefangen'));
            return true;
        }

        $fleet = $ship->getFleet();

        if (
            $fleet !== null
            && $ship->isFleetLeader()
            && $fleet->getDefendedColony() !== null
        ) {
            $game->addInformation(_('Flug während Kolonie-Verteidigung nicht möglich'));

            return true;
        }

        if (
            $fleet !== null
            && $ship->isFleetLeader()
            && $fleet->getBlockedColony() !== null
        ) {
            $game->addInformation(_('Flug während Kolonie-Blockierung nicht möglich'));

            return true;
        }


        return $this->isSanityCheckFaultyConcrete($wrapper, $game);
    }
}
