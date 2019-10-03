<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\InterceptShip;

use request;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Communication\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Communication\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class InterceptShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_INTERCEPT';

    private $shipLoader;

    private $privateMessageSender;

    private $shipRepository;

    private $shipSystemManager;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $target = $this->shipRepository->find(request::indInt('target'));
        if ($target === null) {
            return;
        }
        if (!checkPosition($target, $ship)) {
            return;
        }

        if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrewCount() == 0) {
            $game->addInformationf(
                _("Es werden %d Crewmitglieder benötigt"),
                $ship->getBuildplan()->getCrew()
            );
            return;
        }

        if (!$target->getWarpState()) {
            return;
        }
        if ($target->getUser() === $game->getUser()) {
            return;
        }
        if (!$ship->canIntercept()) {
            return;
        }
        if ($ship->getDockedTo()) {
            $game->addInformation('Das Schiff hat abgedockt');
            $ship->setDockedTo(null);
        }
        if ($target->getFleetId()) {
            foreach ($target->getFleet()->getShips() as $fleetShip) {
                $this->shipSystemManager->deactivate($fleetShip, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);

                $this->shipRepository->save($fleetShip);
            }

            $game->addInformation("Die Flotte " . $target->getFleet()->getName() . " wurde abgefangen");
            $pm = "Die Flotte " . $target->getFleet()->getName() . " wurde von der " . $ship->getName() . " abgefangen";
        } else {
            $this->shipSystemManager->deactivate($target, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);

            $game->addInformation("Die " . $target->getName() . "  wurde abgefangen");
            $pm = "Die " . $target->getName() . " wurde von der " . $ship->getName() . " abgefangen";

            $this->shipRepository->save($target);
        }

        $this->privateMessageSender->send($userId, (int)$target->getUserId(), $pm,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP);
        if ($ship->getFleetId()) {
            foreach ($ship->getFleet()->getShips() as $fleetShip) {
                $this->shipSystemManager->deactivate($fleetShip, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);

                $this->shipRepository->save($fleetShip);
            }
        } else {
            $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);

            $this->shipRepository->save($ship);
        }
        // @todo TBD Red alert
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
