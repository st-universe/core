<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TroopTransfer;

use request;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Crew\CrewEnum;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\Exception\SystemNotActivableException;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\DockPrivilegeUtilityInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\TroopTransferUtilityInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class TroopTransfer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TROOP_TRANSFER';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private TroopTransferUtilityInterface $transferUtility;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private CrewRepositoryInterface $crewRepository;

    private ActivatorDeactivatorHelperInterface $helper;

    private ShipSystemManagerInterface $shipSystemManager;

    private DockPrivilegeUtilityInterface $dockPrivilegeUtility;

    private EntityManagerInterface $entityManager;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ColonyRepositoryInterface $colonyRepository,
        TroopTransferUtilityInterface $transferUtility,
        ShipCrewRepositoryInterface $shipCrewRepository,
        CrewRepositoryInterface $crewRepository,
        ActivatorDeactivatorHelperInterface $helper,
        ShipSystemManagerInterface $shipSystemManager,
        DockPrivilegeUtilityInterface $dockPrivilegeUtility,
        EntityManagerInterface $entityManager,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->colonyRepository = $colonyRepository;
        $this->transferUtility = $transferUtility;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->crewRepository = $crewRepository;
        $this->helper = $helper;
        $this->shipSystemManager = $shipSystemManager;
        $this->dockPrivilegeUtility = $dockPrivilegeUtility;
        $this->entityManager = $entityManager;
        $this->loggerUtil = $loggerUtil;
    }

    public function handle(GameControllerInterface $game): void
    {
        $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);

        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        if (!$ship->hasEnoughCrew()) {
            $game->addInformationf(
                _("Es werden %d Crewmitglieder benötigt"),
                $ship->getBuildplan()->getCrew()
            );
            return;
        }

        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)) {
            $game->addInformation(_("Die Truppenquartiere sind zerstört"));
            return;
        }
        if ($ship->getEps() == 0) {
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

        $isColony = request::has('isColony');
        $isUnload = request::has('isUnload');

        if ($isColony) {
            $target = $this->colonyRepository->find((int)request::postIntFatal('target'));
        } else {
            $target = $this->shipRepository->find((int)request::postIntFatal('target'));
        }


        if ($target === null) {
            return;
        }
        if (!$ship->canInteractWith($target, $isColony, !$isColony)) {
            return;
        }
        if (!$isColony && $target->getWarpState()) {
            $game->addInformation(sprintf(_('Die %s befindet sich im Warp'), $target->getName()));
            return;
        }
        $requestedTransferCount = request::postInt('tcount');

        $amount = 0;

        try {
            if ($isColony) {
                if ($isUnload) {
                    $amount = $this->transferToColony($requestedTransferCount, $ship);
                } else {
                    $amount = $this->transferFromColony($requestedTransferCount, $ship, $game);
                }
            } else {

                $this->loggerUtil->log('A');
                $isUplinkSituation = false;

                if ($target->getUser() !== $user) {
                    $this->loggerUtil->log('B');
                    if ($target->hasUplink()) {
                        $this->loggerUtil->log('C');
                        $isUplinkSituation = true;
                        $ownForeignerCount = $this->transferUtility->ownForeignerCount($user, $target);
                    } else {
                        return;
                    }
                }

                if ($isUnload) {
                    if ($isUplinkSituation && !$this->dockPrivilegeUtility->checkPrivilegeFor($target->getId(), $user)) {
                        $game->addInformation(_("Benötigte Andockerlaubnis wurde verweigert"));
                        return;
                    }

                    $amount = $this->transferToShip($requestedTransferCount, $ship, $target, $isUplinkSituation, $ownForeignerCount, $game);
                } else {
                    $amount = $this->transferFromShip($requestedTransferCount, $ship, $target, $isUplinkSituation, $ownForeignerCount, $game);
                }
            }
        } catch (ShipSystemException $e) {
            return;
        }

        $this->shipRepository->save($ship);

        $game->addInformation(
            sprintf(
                _('Die %s hat %d Crewman %s der %s transferiert'),
                $ship->getName(),
                $amount,
                $isUnload ? 'zu' : 'von',
                $target->getName()
            )
        );

        if ($ship->getCrewCount() <= $ship->getBuildplan()->getCrew()) {
            $this->helper->deactivate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS, $game);
        }
    }

    private function transferToColony(int $requestedTransferCount, ShipInterface $ship): int
    {
        $amount = min($requestedTransferCount, $this->transferUtility->getBeamableTroopCount($ship));

        $array = $ship->getCrewlist()->getValues();

        for ($i = 0; $i < $amount; $i++) {
            $sc = $array[$i];
            $ship->getCrewlist()->removeElement($sc);
            $this->shipCrewRepository->delete($sc);
        }

        return $amount;
    }

    private function transferFromColony(int $requestedTransferCount, ShipInterface $ship, GameControllerInterface $game): int
    {
        $amount = min(
            $requestedTransferCount,
            $ship->getUser()->getFreeCrewCount(),
            $this->transferUtility->getFreeQuarters($ship)
        );

        if ($amount > 0 && $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)->getMode() == ShipSystemModeEnum::MODE_OFF) {
            if (!$this->helper->activate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS, $game)) {
                throw new SystemNotActivableException();
            }
        }

        for ($i = 0; $i < $amount; $i++) {
            $crew = $this->crewRepository->getFreeByUser($game->getUser()->getId());

            $sc = $this->shipCrewRepository->prototype();
            $sc->setCrew($crew);
            $sc->setShip($ship);
            $sc->setUser($ship->getUser());
            $sc->setSlot(CrewEnum::CREW_TYPE_CREWMAN);

            $ship->getCrewlist()->add($sc);

            $this->shipCrewRepository->save($sc);
            $this->entityManager->flush();
        }

        return $amount;
    }

    private function transferToShip(
        int $requestedTransferCount,
        ShipInterface $ship,
        ShipInterface $target,
        bool $isUplinkSituation,
        int $ownForeignerCount,
        GameControllerInterface $game
    ): int {
        if (!$target->hasShipSystem(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)) {
            $game->addInformation(sprintf(_('Die %s hat keine Lebenserhaltungssysteme'), $target->getName()));

            throw new SystemNotFoundException();
        }

        $this->loggerUtil->log(sprintf('ownForeignerCount: %d', $ownForeignerCount));

        $amount = min(
            $requestedTransferCount,
            $this->transferUtility->getBeamableTroopCount($ship),
            $this->transferUtility->getFreeQuarters($target),
            $isUplinkSituation ? ($ownForeignerCount === 0 ? 1 : 0) : PHP_INT_MAX
        );

        $array = $ship->getCrewlist()->getValues();

        for ($i = 0; $i < $amount; $i++) {
            $sc = $array[$i];
            $sc->setShip($target);
            $this->shipCrewRepository->save($sc);

            $ship->getCrewlist()->removeElement($sc);
        }

        if (
            $amount > 0 && $target->getShipSystem(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)->getMode() == ShipSystemModeEnum::MODE_OFF
            && $target->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)
        ) {
            $this->shipSystemManager->activate($target, ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT, true);
        }

        return $amount;
    }

    private function transferFromShip(
        int $requestedTransferCount,
        ShipInterface $ship,
        ShipInterface $target,
        bool $isUplinkSituation,
        int $ownForeignerCount,
        GameControllerInterface $game
    ): int {
        $amount = min(
            $requestedTransferCount,
            $target->getCrewCount(),
            $this->transferUtility->getFreeQuarters($ship),
            $isUplinkSituation ? $ownForeignerCount : PHP_INT_MAX
        );

        if ($amount > 0 && $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)->getMode() == ShipSystemModeEnum::MODE_OFF) {
            if (!$this->helper->activate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS, $game)) {
                throw new SystemNotActivableException();
            }
        }

        $array = $target->getCrewlist()->getValues();
        $targetCrewCount = $target->getCrewCount();

        for ($i = 0; $i < $amount; $i++) {
            $sc = $array[$i];
            $sc->setShip($ship);
            $this->shipCrewRepository->save($sc);

            $ship->getCrewlist()->add($sc);
        }

        // no crew left
        if ($amount == $targetCrewCount) {
            $this->shipSystemManager->deactivateAll($target);
            $target->setAlertState(1);

            foreach ($target->getDockedShips() as $dockedShip) {
                $dockedShip->setDockedTo(null);
                $this->shipRepository->save($dockedShip);
            }

            $target->getDockedShips()->clear();
            $this->shipRepository->save($target);
        }

        return $amount;
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
