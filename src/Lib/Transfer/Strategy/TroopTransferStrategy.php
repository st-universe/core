<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Strategy;

use request;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\Exception\SystemNotActivatableException;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Type\UplinkShipSystem;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\Auxiliary\ShipShutdownInterface;
use Stu\Module\Ship\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Ship\Lib\Interaction\DockPrivilegeUtilityInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Entity\ShipInterface;

class TroopTransferStrategy implements TransferStrategyInterface
{
    private ShipCrewCalculatorInterface $shipCrewCalculator;

    private TroopTransferUtilityInterface $transferUtility;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ShipSystemManagerInterface $shipSystemManager;

    private DockPrivilegeUtilityInterface $dockPrivilegeUtility;

    private ActivatorDeactivatorHelperInterface $helper;

    private ShipShutdownInterface $shipShutdown;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private PrivateMessageSenderInterface $privateMessageSender;

    private LoggerUtilInterface $logger;

    public function __construct(
        ShipCrewCalculatorInterface $shipCrewCalculator,
        TroopTransferUtilityInterface $transferUtility,
        ColonyLibFactoryInterface $colonyLibFactory,
        ShipSystemManagerInterface $shipSystemManager,
        DockPrivilegeUtilityInterface $dockPrivilegeUtility,
        ActivatorDeactivatorHelperInterface $helper,
        ShipShutdownInterface $shipShutdown,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        PrivateMessageSenderInterface $privateMessageSender,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipCrewCalculator = $shipCrewCalculator;
        $this->transferUtility = $transferUtility;
        $this->transferUtility = $transferUtility;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->shipSystemManager = $shipSystemManager;
        $this->dockPrivilegeUtility = $dockPrivilegeUtility;
        $this->helper = $helper;
        $this->shipShutdown = $shipShutdown;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->privateMessageSender = $privateMessageSender;

        $this->logger = $loggerUtilFactory->getLoggerUtil();
    }

    public function setTemplateVariables(
        bool $isUnload,
        ShipInterface $ship,
        ShipInterface|ColonyInterface $target,
        GameControllerInterface $game
    ): void {

        $user = $game->getUser();

        if (
            $target instanceof ShipInterface
            && $target->getBuildplan() !== null
        ) {
            $game->setTemplateVar('SHOW_TARGET_CREW', true);
            $game->setTemplateVar('ACTUAL_TARGET_CREW', $target->getCrewCount());
            $game->setTemplateVar('MINIMUM_TARGET_CREW', $target->getBuildplan()->getCrew());
            $game->setTemplateVar(
                'MAXIMUM_TARGET_CREW',
                $this->shipCrewCalculator->getMaxCrewCountByShip($target)
            );
        }

        $isUplinkSituation = false;

        if ($target instanceof ColonyInterface) {
            if ($isUnload) {
                $max = $this->transferUtility->getBeamableTroopCount($ship);
            } else {
                $max = min(
                    $target->getCrewAssignmentAmount(),
                    $this->transferUtility->getFreeQuarters($ship)
                );
            }
        } else {
            $ownCrewOnTarget = $this->transferUtility->ownCrewOnTarget($user, $target);

            if ($target->getUser() !== $user) {
                if ($target->hasUplink()) {
                    $isUplinkSituation = true;
                } else {
                    return;
                }
            }

            if ($isUnload) {
                $max = min(
                    $this->transferUtility->getBeamableTroopCount($ship),
                    $this->transferUtility->getFreeQuarters($target),
                    $isUplinkSituation ? ($ownCrewOnTarget === 0 ? 1 : 0) : PHP_INT_MAX
                );
            } else {
                $max = min(
                    $ownCrewOnTarget,
                    $this->transferUtility->getFreeQuarters($ship)
                );

                echo $ownCrewOnTarget;
            }
        }

        if (!$isUplinkSituation && $target->getUser() !== $ship->getUser()) {
            return;
        }

        $game->setTemplateVar('MAXIMUM', $max);
    }

    public function transfer(
        bool $isUnload,
        ShipWrapperInterface $wrapper,
        ShipInterface|ColonyInterface $target,
        GameControllerInterface $game
    ): void {

        $user = $game->getUser();
        $ship = $wrapper->get();

        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)) {
            if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)) {
                $game->addInformation(_("Die Truppenquartiere sind zerstört"));
                return;
            }
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->getWarpState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        if ($ship->getShieldState()) {
            $game->addInformation(_("Die Schilde sind aktiviert"));
            return;
        }

        $requestedTransferCount = request::postInt('crewcount');
        $isColonyTarget = $target instanceof ColonyInterface;
        $amount = 0;

        try {
            if ($isColonyTarget) {
                if ($isUnload) {
                    $amount = $this->transferToColony($requestedTransferCount, $ship, $target);
                } else {
                    $amount = $this->transferFromColony($requestedTransferCount, $wrapper, $target, $game);
                }
            } else {
                $isUplinkSituation = false;
                $ownCrewOnTarget = $this->transferUtility->ownCrewOnTarget($user, $target);

                if ($target->getUser() !== $user) {
                    if ($target->hasUplink()) {
                        $isUplinkSituation = true;
                    } else {
                        return;
                    }
                }

                if ($isUnload) {
                    if ($isUplinkSituation) {
                        if (!$this->dockPrivilegeUtility->checkPrivilegeFor($target->getId(), $user)) {
                            $game->addInformation(_("Benötigte Andockerlaubnis wurde verweigert"));
                            return;
                        }
                        if (!$target->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_UPLINK)) {
                            $game->addInformation(_("Das Ziel verfügt über keinen intakten Uplink"));
                            return;
                        }

                        if ($this->transferUtility->foreignerCount($target) >= UplinkShipSystem::MAX_FOREIGNERS) {
                            $game->addInformation(_("Maximale Anzahl an fremden Crewman ist bereits erreicht"));
                        }
                    }

                    $amount = $this->transferToShip($requestedTransferCount, $ship, $target, $isUplinkSituation, $ownCrewOnTarget, $game);
                } else {
                    $amount = $this->transferFromShip($requestedTransferCount, $wrapper, $target, $isUplinkSituation, $ownCrewOnTarget, $game);
                }
            }
        } catch (ShipSystemException $e) {
            return;
        }

        $game->addInformation(
            sprintf(
                _('Die %s hat %d Crewman %s der %s transferiert'),
                $ship->getName(),
                $amount,
                $isUnload ? 'zu' : 'von',
                $target->getName()
            )
        );

        if (
            $ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)
            && $ship->getSystemState(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)
            && $ship->getBuildplan() !== null && $ship->getCrewCount() <= $ship->getBuildplan()->getCrew()
        ) {
            $this->helper->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS, $game);
        }
    }


    private function transferToColony(int $requestedTransferCount, ShipInterface $ship, ColonyInterface $colony): int
    {
        $freeAssignmentCount = $this->colonyLibFactory->createColonyPopulationCalculator(
            $colony
        )->getFreeAssignmentCount();

        $amount = min(
            $requestedTransferCount,
            $this->transferUtility->getBeamableTroopCount($ship),
            $freeAssignmentCount
        );

        $assignments = $ship->getCrewAssignments()->getValues();

        for ($i = 0; $i < $amount; $i++) {
            //assign crew to colony
            $this->transferUtility->assignCrew($assignments[$i], $colony);
        }

        return $amount;
    }

    private function transferFromColony(int $requestedTransferCount, ShipWrapperInterface $wrapper, ColonyInterface $colony, GameControllerInterface $game): int
    {
        $ship = $wrapper->get();

        $amount = min(
            $requestedTransferCount,
            $colony->getCrewAssignmentAmount(),
            $this->transferUtility->getFreeQuarters($ship)
        );

        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)) {
            if (
                $amount > 0
                && $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)->getMode() === ShipSystemModeEnum::MODE_OFF
                && !$this->helper->activate($wrapper, ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS, $game)
            ) {
                throw new SystemNotActivatableException();
            }
        }

        $crewAssignments = $colony->getCrewAssignments();

        for ($i = 0; $i < $amount; $i++) {
            /** @var ShipCrewInterface $crewAssignment */
            $crewAssignment = $crewAssignments->get(array_rand($crewAssignments->toArray()));

            $this->transferUtility->assignCrew($crewAssignment, $ship);
        }

        return $amount;
    }

    private function transferToShip(
        int $requestedTransferCount,
        ShipInterface $ship,
        ShipInterface $target,
        bool $isUplinkSituation,
        int $ownCrewOnTarget,
        GameControllerInterface $game
    ): int {
        if (!$target->hasShipSystem(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)) {
            $game->addInformation(sprintf(_('Die %s hat keine Lebenserhaltungssysteme'), $target->getName()));

            throw new SystemNotFoundException();
        }

        $this->logger->log(sprintf(
            'toShip, requested: %d, beamableOfSource: %d, freeOfTarget: %d',
            $requestedTransferCount,
            $this->transferUtility->getBeamableTroopCount($ship),
            $this->transferUtility->getFreeQuarters($target)
        ));

        $amount = min(
            $requestedTransferCount,
            $this->transferUtility->getBeamableTroopCount($ship),
            $this->transferUtility->getFreeQuarters($target),
            $isUplinkSituation ? ($ownCrewOnTarget === 0 ? 1 : 0) : PHP_INT_MAX
        );

        $assignments = $ship->getCrewAssignments()->getValues();

        for ($i = 0; $i < $amount; $i++) {
            $this->transferUtility->assignCrew($assignments[$i], $target);
        }

        if ($amount > 0) {
            if (
                $target->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)
                && $target->getShipSystem(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)->getMode() == ShipSystemModeEnum::MODE_OFF
            ) {
                $this->shipSystemManager->activate($this->shipWrapperFactory->wrapShip($target), ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT, true);
            }

            if ($isUplinkSituation) {
                $target->getShipSystem(ShipSystemTypeEnum::SYSTEM_UPLINK)->setMode(ShipSystemModeEnum::MODE_ON);
                $this->sendUplinkMessage(true, true, $ship, $target);
            }
        }

        return $amount;
    }

    private function transferFromShip(
        int $requestedTransferCount,
        ShipWrapperInterface $wrapper,
        ShipInterface $target,
        bool $isUplinkSituation,
        int $ownCrewOnTarget,
        GameControllerInterface $game
    ): int {
        $ship = $wrapper->get();

        $amount = min(
            $requestedTransferCount,
            $this->transferUtility->getFreeQuarters($ship),
            $ownCrewOnTarget
        );

        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)) {
            if (
                $amount > 0
                && $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)->getMode() === ShipSystemModeEnum::MODE_OFF
                && !$this->helper->activate($wrapper, ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS, $game)
            ) {
                throw new SystemNotActivatableException();
            }
        }

        $array = $target->getCrewAssignments()->getValues();
        $targetCrewCount = $target->getCrewCount();

        $i = 0;
        foreach ($array as $crewAssignment) {
            if ($crewAssignment->getCrew()->getUser() !== $ship->getUser()) {
                continue;
            }

            $this->transferUtility->assignCrew($crewAssignment, $ship);
            $i++;

            if ($i === $amount) {
                break;
            }
        }

        if ($amount > 0 && $isUplinkSituation) {
            //no foreigners left, shut down uplink
            if ($this->transferUtility->foreignerCount($target) === 0) {
                $target->getShipSystem(ShipSystemTypeEnum::SYSTEM_UPLINK)->setMode(ShipSystemModeEnum::MODE_OFF);
                $this->sendUplinkMessage(false, false, $ship, $target);
            } else {
                $this->sendUplinkMessage(false, true, $ship, $target);
            }
        }

        $targetWrapper = $this->shipWrapperFactory->wrapShip($target);

        // no crew left
        if ($amount === $targetCrewCount) {
            $this->shipShutdown->shutdown($targetWrapper);
        } elseif (
            $target->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)
            && $target->getSystemState(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)
            && $target->getBuildplan() !== null && $target->getCrewCount() <= $target->getBuildplan()->getCrew()
        ) {
            $this->helper->deactivate($targetWrapper, ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS, $game);
        }

        return $amount;
    }

    private function sendUplinkMessage(bool $isUnload, bool $isOn, ShipInterface $ship, ShipInterface $target): void
    {
        $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $target->getId());

        $msg = sprintf(
            _('Die %s von Spieler %s hat 1 Crewman %s deiner Station %s gebeamt. Der Uplink ist %s'),
            $ship->getName(),
            $ship->getUser()->getName(),
            $isUnload ? 'zu' : 'von',
            $target->getName(),
            $isOn ? 'aktiviert' : 'deaktiviert'
        );

        $this->privateMessageSender->send(
            $ship->getUser()->getId(),
            $target->getUser()->getId(),
            $msg,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION,
            $href
        );
    }
}
