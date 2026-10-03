<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ChangeFleetFixation;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class ChangeFleetFixation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_FLEET_FIXATION';

    public function __construct(private ShipLoaderInterface $shipLoader) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $context->getUser()->getId()
        );

        $fleet = $ship->getFleet();
        if ($fleet === null) {
            return;
        }
        if (!$ship->isFleetLeader()) {
            return;
        }

        $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        if (request::postString('fleetfixed') !== false) {
            $fleet->setIsFleetFixed(true);
            $context->getInfo()->addInformation(_('Die Flotte ist nun fixiert'));
        } else {
            $fleet->setIsFleetFixed(false);
            $context->getInfo()->addInformation(_('Die Flotte ist nun nicht mehr fixiert'));
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
