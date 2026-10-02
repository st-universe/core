<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LeaveFleet;

use Stu\Exception\EntityLockedException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowInformation\ShowInformation;
use Stu\Orm\Entity\Ship;

final class LeaveFleet implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_LEAVE_FLEET';

    public function __construct(
        private LeaveFleetRequestInterface $leaveFleetRequest,
        private ShipLoaderInterface $shipLoader
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowInformation::VIEW_IDENTIFIER);

        try {
            $ship = $this->shipLoader->getByIdAndUser(
                $this->leaveFleetRequest->getShipId(),
                $context->getUser()->getId()
            );

            $this->entferneSchiffAusFlotte($ship, $context);
        } catch (EntityLockedException $e) {
            $context->getInfo()->addInformation($e->getMessage());
        }
    }

    private function entferneSchiffAusFlotte(Ship $ship, ActionControllerContext $context): void
    {
        $fleet = $ship->getFleet();
        if ($fleet === null) {
            return;
        }
        if ($ship->isFleetLeader()) {
            return;
        }

        $context->addExecuteJS(sprintf('refreshShiplistFleet(%d);', $ship->getFleetId()));
        $context->addExecuteJS('refreshShiplistSingles();');

        // Initialize the fleet's ships collection to ensure Doctrine properly tracks the removal
        // This is necessary due to Doctrine's lazy-loading behavior with inverse-side OneToMany relationships
        $fleet->getShips()->getValues();

        $ship->setFleet(null);

        $context->getInfo()->addInformation(
            sprintf(_('Die %s hat die Flotte verlassen'), $ship->getName())
        );
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
