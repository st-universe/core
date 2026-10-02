<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\MoveShip;

use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\RandomSystemEntryInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Ship;

abstract class AbstractDirectedMovement implements ActionControllerInterface
{
    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        protected MoveShipRequestInterface $moveShipRequest,
        private SpacecraftLoaderInterface $spacecraftLoader,
        private ShipMoverInterface $shipMover,
        protected FlightRouteFactoryInterface $flightRouteFactory,
        protected RandomSystemEntryInterface $randomSystemEntry,
        private DistributedMessageSenderInterface $distributedMessageSender
    ) {}

    abstract protected function isSanityCheckFaultyConcrete(SpacecraftWrapperInterface $wrapper, ActionControllerContext $context): bool;

    abstract protected function getFlightRoute(SpacecraftWrapperInterface $wrapper): FlightRouteInterface;

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $context->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            $this->moveShipRequest->getShipId(),
            $userId
        );

        if ($this->isSanityCheckFaulty($wrapper, $context)) {
            return;
        }

        $ship = $wrapper->get();

        $messages = $this->shipMover->checkAndMove(
            $wrapper,
            $this->getFlightRoute($wrapper)
        );
        $context->getInfo()->addInformationWrapper($messages->getInformationDump());


        $this->distributedMessageSender->distributeMessageCollection(
            $messages,
            $userId,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );


        if ($ship->getCondition()->isDestroyed()) {
            $context->setView(ModuleEnum::SHIP);
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }

    private function isSanityCheckFaulty(SpacecraftWrapperInterface $wrapper, ActionControllerContext $context): bool
    {
        $spacecraft = $wrapper->get();

        if (!$spacecraft->hasEnoughCrew($context)) {
            return true;
        }

        if ($spacecraft->getHoldingWeb() !== null && $spacecraft->getHoldingWeb()->isFinished()) {
            $context->getInfo()->addInformation(_('Das Schiff ist in einem Energienetz gefangen'));
            return true;
        }

        if ($spacecraft instanceof Ship) {

            if ($spacecraft->isTractored()) {
                $context->getInfo()->addInformation(_('Das Schiff wird von einem Traktorstrahl gehalten'));
                return true;
            }


            $fleet = $spacecraft->getFleet();

            if (
                $fleet !== null
                && $spacecraft->isFleetLeader()
                && $fleet->getDefendedColony() !== null
            ) {
                $context->getInfo()->addInformation(_('Flug während Kolonie-Verteidigung nicht möglich'));

                return true;
            }

            if (
                $fleet !== null
                && $spacecraft->isFleetLeader()
                && $fleet->getBlockedColony() !== null
            ) {
                $context->getInfo()->addInformation(_('Flug während Kolonie-Blockierung nicht möglich'));

                return true;
            }
        }

        return $this->isSanityCheckFaultyConcrete($wrapper, $context);
    }
}
