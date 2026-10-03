<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\CreateFleet;

use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class CreateFleet implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_NEW_FLEET';

    public function __construct(
        private readonly CreateFleetRequestInterface $createFleetRequest,
        private readonly FleetRepositoryInterface $fleetRepository,
        private readonly ShipLoaderInterface $shipLoader,
        private readonly UserSettingsProviderInterface $userSettingsProvider
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $spacecraft = $this->shipLoader->getByIdAndUser($this->createFleetRequest->getShipId(), $context->getUser()->getId());

        if ($spacecraft->getFleetId()) {
            return;
        }
        if ($spacecraft->getCondition()->isUnderRetrofit()) {
            $context->getInfo()->addInformation(_('Aktion nicht möglich, da das Schiff umgerüstet wird.'));
            return;
        }
        if ($spacecraft->isTractored()) {
            $context->getInfo()->addInformation(
                _('Aktion nicht möglich, da Schiff von einem Traktorstrahl gehalten wird.'),
            );
            return;
        }

        if ($spacecraft->getTakeoverPassive() !== null) {
            $context->getInfo()->addInformation(
                _('Aktion nicht möglich, da Schiff im Begriff ist übernommen zu werden.'),
            );
            return;
        }

        $fleet = $this->fleetRepository->prototype();
        $fleet->setLeadShip($spacecraft);
        $fleet->setUser($context->getUser());
        $fleet->setName(_('Flotte'));
        $fleet->setSort($this->fleetRepository->getHighestSortByUser($context->getUser()->getId()));
        $fleet->setIsFleetFixed($this->userSettingsProvider->getFleetFixedDefault($context->getUser()));

        $this->fleetRepository->save($fleet);

        $spacecraft->setFleet($fleet);
        $spacecraft->setIsFleetLeader(true);

        $context->getInfo()->addInformation(_('Die Flotte wurde erstellt'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
