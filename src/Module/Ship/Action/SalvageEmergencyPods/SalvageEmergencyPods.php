<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SalvageEmergencyPods;

use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Interaction\InteractionChecker;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class SalvageEmergencyPods implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SALVAGE_EPODS';

    private SalvageEmergencyPodsRequestInterface $request;

    private ShipLoaderInterface $shipLoader;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private TradePostRepositoryInterface $tradePostRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private TroopTransferUtilityInterface $troopTransferUtility;

    private CancelRepairInterface $cancelRepair;

    private TransferToClosestLocation $transferToClosestLocation;

    public function __construct(
        SalvageEmergencyPodsRequestInterface $request,
        ShipLoaderInterface $shipLoader,
        ShipCrewRepositoryInterface $shipCrewRepository,
        TradePostRepositoryInterface $tradePostRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        TroopTransferUtilityInterface  $troopTransferUtility,
        CancelRepairInterface $cancelRepair,
        TransferToClosestLocation $transferToClosestLocation
    ) {
        $this->request = $request;
        $this->shipLoader = $shipLoader;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->tradePostRepository = $tradePostRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->troopTransferUtility = $troopTransferUtility;
        $this->cancelRepair = $cancelRepair;
        $this->transferToClosestLocation = $transferToClosestLocation;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $this->request->getShipId(),
            $userId,
            $this->request->getTargetId()
        );

        $wrapper = $wrappers->getSource();
        $ship = $wrapper->get();
        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        if (!InteractionChecker::canInteractWith($ship, $target, $game)) {
            throw new SanityCheckException('can not interact with target', self::ACTION_IDENTIFIER);
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($target->getCrewCount() == 0) {
            $game->addInformation(_('Keine Rettungskapseln vorhanden'));
            return;
        }
        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() < 1) {
            $game->addInformation(sprintf(_('Zum Bergen der Rettungskapseln wird %d Energie benötigt'), 1));
            return;
        }
        if ($this->cancelRepair->cancelRepair($ship)) {
            $game->addInformation("Die Reparatur wurde abgebrochen");
        }



        $crewmanPerUser = $this->determineCrewmanPerUser($target);
        if ($crewmanPerUser === []) {
            return;
        }

        $closestTradepost = $this->tradePostRepository->getClosestNpcTradePost($ship->getLocation());
        if ($closestTradepost === null) {
            $game->addInformation('Kein Handelposten in der Nähe, an den die Crew überstellt werden könnte');
            return;
        }

        //send PMs to crew owners
        $this->sendPMsToCrewOwners($crewmanPerUser, $ship, $target, $closestTradepost, $game);

        /**
         * TODO
         //remove entity if crew was on escape pods
         if ($target->getRump()->isEscapePods())
         {
             echo "- removeEscapePodEntity\n";
             $this->shipRemover->remove($target);
            } else
            {
            }
         */

        $epsSystem->lowerEps(1)->update();

        $this->shipLoader->save($ship);
    }

    /**
     * @return array<int, int>
     */
    private function determineCrewmanPerUser(ShipInterface $target): array
    {
        $crewmanPerUser = [];

        foreach ($target->getCrewAssignments() as $shipCrew) {
            $crewUserId = $shipCrew->getCrew()->getUser()->getId();

            if (!array_key_exists($crewUserId, $crewmanPerUser)) {
                $crewmanPerUser[$crewUserId] = 1;
            } else {
                $crewmanPerUser[$crewUserId]++;
            }
        }

        return $crewmanPerUser;
    }

    /**
     * @param array<int, int> $crewmanPerUser
     */
    private function sendPMsToCrewOwners(
        array $crewmanPerUser,
        ShipInterface $ship,
        ShipInterface $target,
        TradePostInterface $closestTradepost,
        GameControllerInterface $game
    ): void {
        $userId = $game->getUser()->getId();

        $sentGameInfoForForeignCrew = false;

        foreach ($crewmanPerUser as $ownerId => $count) {
            if ($ownerId !== $userId) {
                $this->privateMessageSender->send(
                    UserEnum::USER_NOONE,
                    $ownerId,
                    sprintf(
                        _('Der Siedler %s hat %d deiner Crewmitglieder aus Rettungskapseln geborgen und an den Handelsposten "%s" (%s) überstellt.'),
                        $game->getUser()->getName(),
                        $count,
                        $closestTradepost->getName(),
                        $closestTradepost->getShip()->getSectorString()
                    ),
                    PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM
                );
                foreach ($target->getCrewAssignments() as $crewAssignment) {
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
            } elseif ($this->gotEnoughFreeTroopQuarters($ship, $count)) {
                foreach ($target->getCrewAssignments() as $crewAssignment) {
                    if ($crewAssignment->getCrew()->getUser() === $game->getUser()) {
                        $crewAssignment->setShip($ship);
                        $ship->getCrewAssignments()->add($crewAssignment);
                        $this->shipCrewRepository->save($crewAssignment);
                    }
                }
                $game->addInformationf(_('%d eigene Crewman wurde(n) auf dieses Schiff gerettet'), $count);
            } else {
                $game->addInformation($this->transferToClosestLocation->transfer(
                    $ship,
                    $target,
                    $count,
                    $closestTradepost
                ));
            }
        }
    }

    private function gotEnoughFreeTroopQuarters(ShipInterface $ship, int $count): bool
    {
        return $this->troopTransferUtility->getFreeQuarters($ship) >= $count;
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
