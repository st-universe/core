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
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class SalvageEmergencyPods implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SALVAGE_EPODS';

    private ShipLoaderInterface $shipLoader;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private TradePostRepositoryInterface $tradePostRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private TroopTransferUtilityInterface $troopTransferUtility;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipCrewRepositoryInterface $shipCrewRepository,
        TradePostRepositoryInterface $tradePostRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        TroopTransferUtilityInterface  $troopTransferUtility
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->tradePostRepository = $tradePostRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->troopTransferUtility = $troopTransferUtility;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $targetId = request::postIntFatal('target');

        $shipArray = $this->shipLoader->getByIdAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $ship = $shipArray[$shipId];
        $target = $shipArray[$targetId];
        if ($target === null) {
            return;
        }
        $ship->canInteractWith($target);

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

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

        $this->shipLoader->save($ship);
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
        $closestTradepost = $this->tradePostRepository->getClosestTradePost($ship->getCx(), $ship->getCy());

        $sentGameInfoForForeignCrew = false;

        foreach ($crewmanPerUser as $ownerId => $count) {

            if ($ownerId !== $userId) {
                $this->privateMessageSender->send(
                    GameEnum::USER_NOONE,
                    $ownerId,
                    sprintf(
                        _('Der Siedler %s hat %d deiner Crewmitglieder aus Rettungskapseln geborgen und an den Handelsposten "%s" (%s) überstellt.'),
                        $game->getUser()->getName(),
                        $count,
                        $closestTradepost->getName(),
                        $closestTradepost->getShip()->getSectorString()
                    ),
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM
                );
                foreach ($target->getCrewlist() as $crewAssignment) {
                    if ($crewAssignment->getCrew()->getUser()->getId() === $ownerId) {
                        $crewAssignment->setShip(null);
                        $crewAssignment->setTradepost($closestTradepost);
                        $this->shipCrewRepository->save($crewAssignment);
                    }
                }
                if (!$sentGameInfoForForeignCrew) {
                    $game->addInformation(_('Die fremden Crewman wurde geborgen und an den dichtesten Handelsposten überstellt'));
                    $sentGameInfoForForeignCrew = true;
                }
            } else {
                if ($this->gotEnoughFreeTroopQuarters($ship, $count)) {
                    foreach ($target->getCrewlist() as $crewAssignment) {
                        if ($crewAssignment->getCrew()->getUser() === $game->getUser()) {
                            $crewAssignment->setShip($ship);
                            $ship->getCrewlist()->add($crewAssignment);
                            $this->shipCrewRepository->save($crewAssignment);
                        }
                    }
                    $game->addInformationf(_('%d eigene Crewman wurde(n) auf dieses Schiff gerettet'), $count);
                } else {
                    foreach ($target->getCrewlist() as $crewAssignment) {
                        if ($crewAssignment->getCrew()->getUser() === $game->getUser()) {
                            $crewAssignment->setShip(null);
                            $crewAssignment->setTradepost($closestTradepost);
                            $this->shipCrewRepository->save($crewAssignment);
                        }
                    }
                    $game->addInformationf(
                        _('Deine Crew wurde geborgen und an den Handelsposten "%s" (%s) überstellt'),
                        $closestTradepost->getName(),
                        $closestTradepost->getShip()->getSectorString()
                    );
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
