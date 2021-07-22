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
        $ship->cancelRepair();

        $targetUserId = $target->getCrewList()->current()->getCrew()->getUser()->getId();
        if ($targetUserId !== $userId) {
            $this->privateMessageSender->send(
                GameEnum::USER_NOONE,
                $targetUserId,
                sprintf(
                    _('Der Siedler %s hat %d deiner Crewmitglieder aus Rettungskapseln geborgen und an deine Kolonien überstellt.'),
                    $game->getUser()->getName(),
                    $target->getCrewCount()
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM
            );
        }

        if ($this->isOwnCrewAndShipGotFreeTroopQuarters($ship, $target)) {
            foreach ($target->getCrewlist() as $shipCrew) {
                $shipCrew->setShip($ship);
                $this->shipCrewRepository->save($shipCrew);
            }
            $game->addInformation(_('Die Crew wurde auf dieses Schiff gerettet'));
        } else {
            $this->shipCrewRepository->truncateByShip((int) $target->getId());
            $game->addInformation(_('Die Crew wurde geborgen und an den Besitzer überstellt'));
        }

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

    private function isOwnCrewAndShipGotFreeTroopQuarters(ShipInterface $ship, ShipInterface $target): bool
    {
        return $ship->getUser() === current($target->getCrewlist()->getValues())->getCrew()->getUser()
            && $ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)
            && $this->troopTransferUtility->getFreeQuarters($ship) >= $target->getCrewCount();
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
