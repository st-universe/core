<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SalvageEmergencyPods;

use request;
use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\TroopTransferUtilityInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class SalvageEmergencyPods implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SALVAGE_EPODS';

    private ShipLoaderInterface $shipLoader;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipRepositoryInterface $shipRepository;

    private TroopTransferUtilityInterface $troopTransferUtility;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipCrewRepositoryInterface $shipCrewRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository,
        TroopTransferUtilityInterface  $troopTransferUtility
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
        $this->troopTransferUtility = $troopTransferUtility;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $target = $this->shipRepository->find(request::postIntFatal('target'));
        if ($target === null) {
            return;
        }
        $ship->canInteractWith($target);
        if ($target->getCrewCount() == 0) {
            $game->addInformation(_('Keine Rettungskapseln vorhanden'));
            return;
        }
        if ($ship->getEps() < 1) {
            $game->addInformation(sprintf(_('Zum Bergen der Rettungskapseln wird %d Energie benötigt'), 1));
            return;
        }
        if ($ship->cancelRepair()) {
            $game->addInformation("Die Reparatur wurde abgebrochen");
        }

        $crewmanPerUser = $this->determineCrewmanPerUser($target);

        //send PMs to crew owners
        $this->sendPMsToCrewOwners($crewmanPerUser, $ship, $target, $game);

        /**
         * 
         //remove entity if crew was on escape pods
         if ($target->getRump()->isEscapePods())
         {
             echo "- removeEscapePodEntity\n";
             $this->shipRemover->remove($target);
            } else
            {
            }
         */

        $ship->setEps($ship->getEps() - 1);

        $this->shipRepository->save($ship);
    }

    private function determineCrewmanPerUser(ShipInterface $target): array
    {
        $crewmanPerUser = [];

        foreach ($target->getCrewList() as $shipCrew) {
            $crewUserId = $shipCrew->getCrew()->getUser()->getId();

            if (!array_key_exists($crewUserId, $crewmanPerUser)) {
                $crewmanPerUser[$crewUserId] = 1;
            } else {
                $crewmanPerUser[$crewUserId]++;
            }
        }

        return $crewmanPerUser;
    }

    private function sendPMsToCrewOwners(
        array $crewmanPerUser,
        ShipInterface $ship,
        ShipInterface $target,
        GameControllerInterface $game
    ): void {
        $userId = $game->getUser()->getId();

        $sentGameInfoForForeignCrew = false;

        foreach ($crewmanPerUser as $ownerId => $count) {

            if ($ownerId !== $userId) {
                $this->privateMessageSender->send(
                    GameEnum::USER_NOONE,
                    $ownerId,
                    sprintf(
                        _('Der Siedler %s hat %d deiner Crewmitglieder aus Rettungskapseln geborgen und an deine Kolonien überstellt.'),
                        $game->getUser()->getName(),
                        $count
                    ),
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM
                );
                foreach ($target->getCrewlist() as $shipCrew) {
                    if ($shipCrew->getCrew()->getUser()->getId() === $ownerId) {
                        $this->shipCrewRepository->delete($shipCrew);
                    }
                }
                if (!$sentGameInfoForForeignCrew) {
                    $game->addInformation(_('Die fremden Crewman wurde geborgen und an ihre(n) Besitzer überstellt'));
                    $sentGameInfoForForeignCrew = true;
                }
            } else {
                if ($this->gotEnoughFreeTroopQuarters($ship, $count)) {
                    foreach ($target->getCrewlist() as $shipCrew) {
                        if ($shipCrew->getCrew()->getUser() === $game->getUser()) {
                            $shipCrew->setShip($ship);
                            $ship->getCrewlist()->add($shipCrew);
                            $this->shipCrewRepository->save($shipCrew);
                        }
                    }
                    $game->addInformationf(_('%d eigene Crewman wurde(n) auf dieses Schiff gerettet'), $count);
                } else {
                    foreach ($target->getCrewlist() as $shipCrew) {
                        if ($shipCrew->getCrew()->getUser() === $game->getUser()) {
                            $this->shipCrewRepository->delete($shipCrew);
                        }
                    }
                    $game->addInformation(_('Deine Crew wurde geborgen und an deine Kolonien überstellt'));
                }
            }
        }
    }


    private function gotEnoughFreeTroopQuarters(ShipInterface $ship, int $count): bool
    {
        return $ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)
            && $this->troopTransferUtility->getFreeQuarters($ship) >= $count;
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
