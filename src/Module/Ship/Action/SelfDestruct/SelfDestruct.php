<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SelfDestruct;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Ship\Lib\Battle\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\View\Overview\Overview;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class SelfDestruct implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SELFDESTRUCT';

    private ShipLoaderInterface $shipLoader;

    private EntryCreatorInterface $entryCreator;

    private ShipRemoverInterface $shipRemover;

    private ShipRepositoryInterface $shipRepository;

    private AlertRedHelperInterface $alertRedHelper;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        EntryCreatorInterface $entryCreator,
        ShipRemoverInterface $shipRemover,
        ShipRepositoryInterface $shipRepository,
        AlertRedHelperInterface $alertRedHelper
    ) {
        $this->shipLoader = $shipLoader;
        $this->entryCreator = $entryCreator;
        $this->shipRemover = $shipRemover;
        $this->shipRepository = $shipRepository;
        $this->alertRedHelper = $alertRedHelper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $user = $game->getUser();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        if ($ship->isConstruction()) {
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        $code = trim(request::postString('destructioncode'));

        if ($code !== substr(md5($ship->getName()), 0, 6)) {
            $game->addInformation(_('Der Selbstzerstörungscode war fehlerhaft'));
            $game->setView(ShowShip::VIEW_IDENTIFIER);
            return;
        }

        $game->setView(Overview::VIEW_IDENTIFIER);

        $tractoredShipToTriggerAlertRed = ($ship->isTractoring() && $ship->getWarpState()) ? $ship->getTractoredShip() : null;

        $game->addInformation(_('Die Selbstzerstörung war erfolgreich'));
        $msg = sprintf(
            _('Die %s (%s) hat sich in Sektor %s selbst zerstört'),
            $ship->getName(),
            $ship->getRump()->getName(),
            $ship->getSectorString()
        );
        if ($ship->isBase()) {
            $this->entryCreator->addStationEntry(
                $msg,
                $userId
            );
        } else {
            $this->entryCreator->addShipEntry(
                $msg,
                $userId
            );
        }

        $destroyMsg = $this->shipRemover->destroy($wrapper);
        if ($destroyMsg !== null) {
            $game->addInformation($destroyMsg);
        }

        //Alarm-Rot check for tractor ship
        if ($tractoredShipToTriggerAlertRed !== null) {
            $this->alertRedHelper->doItAll($tractoredShipToTriggerAlertRed, $game);
        }

        if ($user->getState() == UserEnum::USER_STATE_COLONIZATION_SHIP && $this->shipRepository->getAmountByUserAndSpecialAbility($userId, ShipRumpSpecialAbilityEnum::COLONIZE) === 1) {
            $user->setState(UserEnum::USER_STATE_UNCOLONIZED);
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
