<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\CreateFleet;

use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
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
    public function handle(GameControllerInterface $game): void
    {
        $spacecraft = $this->shipLoader->getByIdAndUser($this->createFleetRequest->getShipId(), $game->getUser()->getId());

        if ($spacecraft->getFleetId()) {
            return;
        }
        if ($spacecraft->getCondition()->isUnderRetrofit()) {
            $game->getInfo()->addInformation(_('Aktion nicht möglich, da das Schiff umgerüstet wird.'));
            return;
        }
        if ($spacecraft->isTractored()) {
            $game->getInfo()->addInformation(
                _('Aktion nicht möglich, da Schiff von einem Traktorstrahl gehalten wird.'),
            );
            return;
        }

        if ($spacecraft->getTakeoverPassive() !== null) {
            $game->getInfo()->addInformation(
                _('Aktion nicht möglich, da Schiff im Begriff ist übernommen zu werden.'),
            );
            return;
        }

        $fleet = $this->fleetRepository->prototype();
        $fleet->setLeadShip($spacecraft);
        $fleet->setUser($game->getUser());
        $fleet->setName(_('Flotte'));
        $fleet->setSort($this->fleetRepository->getHighestSortByUser($game->getUser()->getId()));
        $fleet->setIsFleetFixed($this->userSettingsProvider->getFleetFixedDefault($game->getUser()));

        $this->fleetRepository->save($fleet);

        $spacecraft->setFleet($fleet);
        $spacecraft->setIsFleetLeader(true);

        $game->getInfo()->addInformation(_('Die Flotte wurde erstellt'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
