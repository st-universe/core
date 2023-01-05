<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SalvageEmergencyPods;

use request;
use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Exception\SanityCheckException;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\InteractionChecker;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\TroopTransferUtilityInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class SalvageEmergencyPods implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SALVAGE_EPODS';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private TradePostRepositoryInterface $tradePostRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private TroopTransferUtilityInterface $troopTransferUtility;

    private CancelRepairInterface $cancelRepair;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipCrewRepositoryInterface $shipCrewRepository,
        TradePostRepositoryInterface $tradePostRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        TroopTransferUtilityInterface  $troopTransferUtility,
        CancelRepairInterface $cancelRepair,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->tradePostRepository = $tradePostRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->troopTransferUtility = $troopTransferUtility;
        $this->cancelRepair = $cancelRepair;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        if ($userId === 102) {
            $this->loggerUtil->init('CREW', LoggerEnum::LEVEL_ERROR);
        }

        $shipId = request::indInt('id');
        $targetId = request::postIntFatal('target');

        $shipArray = $this->shipLoader->getWrappersByIdAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $shipArray[$shipId];
        $ship = $wrapper->get();
        $targetWrapper = $shipArray[$targetId];
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
        if ($epsSystem->getEps() < 1) {
            $game->addInformation(sprintf(_('Zum Bergen der Rettungskapseln wird %d Energie benötigt'), 1));
            return;
        }
        if ($this->cancelRepair->cancelRepair($ship)) {
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

        $epsSystem->setEps($epsSystem->getEps() - 1)->update();

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
        $closestTradepost = $this->tradePostRepository->getClosestNpcTradePost($ship->getCx(), $ship->getCy());

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
                    $game->addInformation($this->transferToClosestLocation(
                        $ship,
                        $target,
                        $count,
                        $closestTradepost
                    ));
                }
            }
        }
    }

    private function gotEnoughFreeTroopQuarters(ShipInterface $ship, int $count): bool
    {
        return $this->troopTransferUtility->getFreeQuarters($ship) >= $count;
    }

    private function transferToClosestLocation(ShipInterface $ship, ShipInterface $target, int $count, TradePostInterface $closestTradepost): string
    {
        [$colonyDistance, $colony] = $this->searchClosestUsableColony($ship, $count);
        [$stationDistance, $station] = $this->searchClosestUsableStation($ship, $count);
        $tradepostDistance = $this->calculateDistance(
            $ship->getCx(),
            $closestTradepost->getShip()->getCx(),
            $ship->getCy(),
            $closestTradepost->getShip()->getCy()
        );

        //$this->loggerUtil->log(sprintf('distance: %d, closestColony: %s', $colonyDistance, $colony->getName()));
        //$this->loggerUtil->log(sprintf('distance: %d, closestStation: %s', $stationDistance, $station->getName()));
        //$this->loggerUtil->log(sprintf('distance: %d, closestTradepost: %s', $tradepostDistance, $closestTradepost->getName()));

        //transfer to closest colony
        if ($colony !== null && $colonyDistance <= $stationDistance && $colonyDistance <= $tradepostDistance) {
            foreach ($target->getCrewlist() as $crewAssignment) {
                if ($crewAssignment->getCrew()->getUser() === $ship->getUser()) {
                    $crewAssignment->setColony($colony);
                    $crewAssignment->setShip(null);
                    $this->shipCrewRepository->save($crewAssignment);
                }
            }
            return sprintf(
                _('Deine Crew wurde geborgen und an die Kolonie "%s" (%s) überstellt'),
                $colony->getName(),
                $colony->getSectorString()
            );
        }

        //transfer to closest station
        if ($station !== null && $stationDistance <= $tradepostDistance) {
            foreach ($target->getCrewlist() as $crewAssignment) {
                if ($crewAssignment->getCrew()->getUser() === $ship->getUser()) {
                    $crewAssignment->setShip($station);
                    $this->shipCrewRepository->save($crewAssignment);
                }
            }
            return sprintf(
                _('Deine Crew wurde geborgen und an die Station "%s" (%s) überstellt'),
                $station->getName(),
                $station->getSectorString()
            );
        }

        //transfer to closest tradepost
        foreach ($target->getCrewlist() as $crewAssignment) {
            if ($crewAssignment->getCrew()->getUser() === $ship->getUser()) {
                $crewAssignment->setShip(null);
                $crewAssignment->setTradepost($closestTradepost);
                $this->shipCrewRepository->save($crewAssignment);
            }
        }
        return sprintf(
            _('Deine Crew wurde geborgen und an den Handelsposten "%s" (%s) überstellt'),
            $closestTradepost->getName(),
            $closestTradepost->getShip()->getSectorString()
        );
    }

    private function searchClosestUsableStation(ShipInterface $ship, int $count): ?array
    {
        $result = [424242, null];
        $stations = $this->shipRepository->getStationsByUser($ship->getUser()->getId());
        foreach ($stations as $station) {
            $freeQuarters = $this->troopTransferUtility->getFreeQuarters($station);

            if ($station->hasEnoughCrew() && $freeQuarters >= $count) {
                $distance = $this->calculateDistance(
                    $ship->getCx(),
                    $station->getCx(),
                    $ship->getCy(),
                    $station->getCy(),
                    $ship->getSystem() !== null ? $ship->getSx() : 0,
                    $station->getSystem() !== null ? $station->getSx() : 0,
                    $ship->getSystem() !== null ? $ship->getSy() : 0,
                    $station->getSystem() !== null ? $station->getSy() : 0,
                );

                if ($result === null || $distance < $result[0]) {
                    $result = [$distance, $station];
                }
            }
        }

        return $result;
    }

    private function searchClosestUsableColony(ShipInterface $ship, int $count): ?array
    {
        $result = [424242, null];
        $colonies = $ship->getUser()->getColonies();
        foreach ($colonies as $colony) {
            $freeQuarters = $colony->getCrewLimit() - $colony->getCrewAssignmentAmount();

            if ($freeQuarters >= $count) {
                $distance = $this->calculateDistance(
                    $ship->getCx(),
                    $colony->getSystem()->getCx(),
                    $ship->getCy(),
                    $colony->getSystem()->getCy(),
                    $ship->getSystem() !== null ? $ship->getSx() : 0,
                    $colony->getSx(),
                    $ship->getSystem() !== null ? $ship->getSy() : 0,
                    $colony->getSy()
                );

                //add one distance if outside of system
                if ($ship->getSystem() === null) {
                    $distance += 1;
                }

                if ($result === null || $distance < $result[0]) {
                    $result = [$distance, $colony];
                }
            }
        }

        return $result;
    }

    private function calculateDistance(
        int $cx1,
        int $cx2,
        int $cy1,
        int $cy2,
        int $sx1 = 0,
        int $sx2 = 0,
        int $sy1 = 0,
        int $sy2 = 0
    ): int {
        $distance = (abs($cx1 - $cx2) + abs($cy1 - $cy2)) * 10000;

        if ($distance !== 0) {
            return $distance;
        }

        return abs($sx1 - $sx2) + abs($sy1 - $sy2);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
