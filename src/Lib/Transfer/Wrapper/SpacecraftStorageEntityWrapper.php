<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Wrapper;

use Override;
use request;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\Type\UplinkShipSystem;
use Stu\Component\Station\Dock\DockPrivilegeUtilityInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Lib\Transfer\CommodityTransferInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\ShipShutdownInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;
use Stu\Orm\Entity\UserInterface;

class SpacecraftStorageEntityWrapper implements StorageEntityWrapperInterface
{
    private SpacecraftInterface $spacecraft;

    public function __construct(
        private ShipTorpedoManagerInterface $shipTorpedoManager,
        private PirateReactionInterface $pirateReaction,
        private CommodityTransferInterface $commodityTransfer,
        private TroopTransferUtilityInterface $troopTransferUtility,
        private DockPrivilegeUtilityInterface $dockPrivilegeUtility,
        private ActivatorDeactivatorHelperInterface $activatorDeactivatorHelper,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private SpacecraftCrewCalculatorInterface $shipCrewCalculator,
        private ShipShutdownInterface $shipShutdown,
        private PrivateMessageSenderInterface $privateMessageSender,
        private SpacecraftWrapperInterface $spacecraftWrapper
    ) {
        $this->spacecraft = $spacecraftWrapper->get();
    }

    // GENERAL
    #[Override]
    public function get(): EntityWithStorageInterface
    {
        return $this->spacecraft;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->spacecraft->getUser();
    }

    #[Override]
    public function getName(): string
    {
        return $this->spacecraft->getName();
    }

    #[Override]
    public function canTransfer(InformationInterface $information): bool
    {
        if (!$this->spacecraft->hasEnoughCrew()) {
            $information->addInformation("Ungenügend Crew vorhanden");
            return false;
        }

        return true;
    }

    #[Override]
    public function getLocation(): LocationInterface
    {
        return $this->spacecraft->getLocation();
    }

    #[Override]
    public function canPenetrateShields(UserInterface $user, InformationInterface $information): bool
    {
        return true;
    }

    // COMMODITIES
    #[Override]
    public function getBeamFactor(): int
    {
        return $this->spacecraft->getBeamFactor();
    }

    #[Override]
    public function transfer(
        bool $isUnload,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): void {

        $hasTransfered = false;

        // check for fleet option
        $fleetWrapper = $this->spacecraftWrapper->getFleetWrapper();
        if (request::postInt('isfleet') && $fleetWrapper !== null) {
            foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
                if ($this->transferPerSpacecraft(
                    $isUnload,
                    $wrapper,
                    $target,
                    $information
                )) {
                    $hasTransfered = true;
                }
            }
        } else {
            $hasTransfered =  $this->transferPerSpacecraft($isUnload, $this->spacecraftWrapper, $target, $information);
        }

        $targetEntity = $target->get();
        if (
            !$isUnload
            && $hasTransfered
            && $this->spacecraft instanceof ShipInterface
            && $targetEntity instanceof ShipInterface
        ) {
            $this->pirateReaction->checkForPirateReaction(
                $targetEntity,
                PirateReactionTriggerEnum::ON_BEAM,
                $this->spacecraft
            );
        }
    }

    private function transferPerSpacecraft(
        bool $isUnload,
        SpacecraftWrapperInterface $wrapper,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): bool {

        $ship = $wrapper->get();
        $epsSystem = $wrapper->getEpsSystemData();

        //sanity checks
        $isDockTransfer = $this->commodityTransfer->isDockTransfer($ship, $target->get());
        if (!$isDockTransfer && ($epsSystem === null || $epsSystem->getEps() === 0)) {
            $information->addInformation("Keine Energie vorhanden");
            return false;
        }
        if ($ship->getCloakState()) {
            $information->addInformation("Die Tarnung ist aktiviert");
            return false;
        }
        if ($ship->isWarped()) {
            $information->addInformation("Schiff befindet sich im Warp");
            return false;
        }

        $transferTarget = $isUnload ? $target->get() : $ship;
        if ($transferTarget->getMaxStorage() <= $transferTarget->getStorageSum()) {
            $information->addInformationf('%s: Der Lagerraum ist voll', $isUnload ? $target->getName() : $ship->getName());
            return false;
        }

        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');

        $storage = $isUnload ? $ship->getBeamableStorage() : $target->get()->getBeamableStorage();

        if ($storage->isEmpty()) {
            $information->addInformation("Keine Waren zum Beamen vorhanden");
            return false;
        }
        if (count($commodities) == 0 || count($gcount) == 0) {
            $information->addInformation("Es wurden keine Waren zum Beamen ausgewählt");
            return false;
        }
        $information->addInformationf(
            'Die %s hat folgende Waren %s %s %s transferiert',
            $ship->getName(),
            $isUnload ? 'zur' : 'von der',
            $target->get()->getTransferEntityType()->getName(),
            $target->getName()
        );

        $hasTransfered = false;
        foreach ($commodities as $key => $value) {
            $commodityId = (int) $value;

            if (!array_key_exists($key, $gcount)) {
                continue;
            }

            if ($this->commodityTransfer->transferCommodity(
                $commodityId,
                $gcount[$key],
                $wrapper,
                $isUnload ? $ship : $target->get(),
                $transferTarget,
                $information
            )) {
                $hasTransfered = true;
            }
        }

        return $hasTransfered;
    }

    // CREW
    #[Override]
    public function getMaxTransferrableCrew(bool $isTarget, UserInterface $user): int
    {
        return min(
            $this->troopTransferUtility->ownCrewOnTarget($user, $this->spacecraft),
            $isTarget ? PHP_INT_MAX : $this->troopTransferUtility->getBeamableTroopCount($this->spacecraft)
        );
    }

    #[Override]
    public function getFreeCrewSpace(UserInterface $user): int
    {
        if ($user !== $this->spacecraft->getUser()) {
            if (!$this->spacecraft->hasUplink()) {
                return 0;
            }

            $userCrewOnTarget = $this->troopTransferUtility->ownCrewOnTarget($user, $this->spacecraft);
            return $userCrewOnTarget === 0 ? 1 : 0;
        }

        return $this->troopTransferUtility->getFreeQuarters($this->spacecraft);
    }

    #[Override]
    public function checkCrewStorage(int $amount, bool $isUnload, InformationInterface $information): bool
    {
        if (!$this->spacecraft->hasShipSystem(SpacecraftSystemTypeEnum::SYSTEM_TROOP_QUARTERS)) {
            return true;
        }

        $maxRumpCrew = $this->shipCrewCalculator->getMaxCrewCountByRump($this->spacecraft->getRump());
        $newCrewAmount = $this->spacecraft->getCrewCount() + ($isUnload ? -$amount : $amount);
        if ($newCrewAmount <= $maxRumpCrew) {
            return true;
        }

        if (!$this->spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::SYSTEM_TROOP_QUARTERS)) {
            $information->addInformation("Die Truppenquartiere sind zerstört");
            return false;
        }

        if ($this->spacecraft->getShipSystem(SpacecraftSystemTypeEnum::SYSTEM_TROOP_QUARTERS)->getMode() > SpacecraftSystemModeEnum::MODE_OFF) {
            return true;
        }

        if (!$this->activatorDeactivatorHelper->activate(
            $this->spacecraftWrapper,
            SpacecraftSystemTypeEnum::SYSTEM_TROOP_QUARTERS,
            $information
        )) {
            $information->addInformation("Die Truppenquartiere konnten nicht aktiviert werden");
            return false;
        }

        return true;
    }

    #[Override]
    public function acceptsCrewFrom(int $amount, UserInterface $user, InformationInterface $information): bool
    {
        if (!$this->spacecraft->hasShipSystem(SpacecraftSystemTypeEnum::SYSTEM_LIFE_SUPPORT)) {
            $information->addInformationf('Die %s hat keine Lebenserhaltungssysteme', $this->spacecraft->getName());

            return false;
        }

        $needsTroopQuarters = $this->spacecraft->getCrewCount() + $amount > $this->shipCrewCalculator->getMaxCrewCountByRump($this->spacecraft->getRump());
        if (
            $needsTroopQuarters
            && $this->spacecraft->hasShipSystem(SpacecraftSystemTypeEnum::SYSTEM_TROOP_QUARTERS)
            && $this->spacecraft->getShipSystem(SpacecraftSystemTypeEnum::SYSTEM_TROOP_QUARTERS)->getMode() === SpacecraftSystemModeEnum::MODE_OFF
            && !$this->activatorDeactivatorHelper->activate($this->spacecraftWrapper, SpacecraftSystemTypeEnum::SYSTEM_TROOP_QUARTERS, $information)
        ) {
            return false;
        }

        if ($this->spacecraft->getUser() === $user) {
            return true;
        }
        if (!$this->spacecraft->hasUplink()) {
            return false;
        }

        if (!$this->dockPrivilegeUtility->checkPrivilegeFor($this->spacecraft->getId(), $user)) {
            $information->addInformation("Benötigte Andockerlaubnis wurde verweigert");
            return false;
        }
        if (!$this->spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::SYSTEM_UPLINK)) {
            $information->addInformation("Das Ziel verfügt über keinen intakten Uplink");
            return false;
        }

        if ($this->troopTransferUtility->foreignerCount($this->spacecraft) >= UplinkShipSystem::MAX_FOREIGNERS) {
            $information->addInformation("Maximale Anzahl an fremden Crewman ist bereits erreicht");
            return false;
        }

        return true;
    }

    #[Override]
    public function postCrewTransfer(int $foreignCrewChangeAmount, StorageEntityWrapperInterface $other, InformationInterface $information): void
    {
        // no crew left, so shut down
        if ($this->spacecraft->getCrewCount() === 0) {
            $this->shipShutdown->shutdown($this->spacecraftWrapper);
            return;
        }

        if ($foreignCrewChangeAmount !== 0) {

            $isOn = $this->troopTransferUtility->foreignerCount($this->spacecraft) > 0;
            if (
                !$isOn
                && $this->spacecraft->getSystemState(SpacecraftSystemTypeEnum::SYSTEM_UPLINK)
            ) {
                $this->spacecraft->getShipSystem(SpacecraftSystemTypeEnum::SYSTEM_UPLINK)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            }

            if ($foreignCrewChangeAmount > 0) {
                $this->sendUplinkMessage($foreignCrewChangeAmount, $isOn, $other);
            } else {

                $this->sendUplinkMessage($foreignCrewChangeAmount, $isOn, $other);
            }
        }

        if (
            $this->spacecraft->hasShipSystem(SpacecraftSystemTypeEnum::SYSTEM_TROOP_QUARTERS)
            && $this->spacecraft->getSystemState(SpacecraftSystemTypeEnum::SYSTEM_TROOP_QUARTERS)
            && $this->spacecraft->getBuildplan() !== null
            && $this->spacecraft->getCrewCount() <= $this->shipCrewCalculator->getMaxCrewCountByRump($this->spacecraft->getRump())
        ) {
            $this->activatorDeactivatorHelper->deactivate($this->spacecraftWrapper, SpacecraftSystemTypeEnum::SYSTEM_TROOP_QUARTERS, $information);
        }

        if (!$this->spacecraft->hasShipSystem(SpacecraftSystemTypeEnum::SYSTEM_LIFE_SUPPORT)) {
            return;
        }

        if ($this->spacecraft->getCrewCount() === 0) {
            $this->spacecraftSystemManager->deactivate($this->spacecraftWrapper, SpacecraftSystemTypeEnum::SYSTEM_LIFE_SUPPORT, true);
            return;
        }

        if (
            $this->spacecraft->getCrewCount() > 0
            && !$this->spacecraft->getSystemState(SpacecraftSystemTypeEnum::SYSTEM_LIFE_SUPPORT)
        ) {
            $this->spacecraftSystemManager->activate($this->spacecraftWrapper, SpacecraftSystemTypeEnum::SYSTEM_LIFE_SUPPORT, true);
        }
    }

    private function sendUplinkMessage(int $foreignCrewChangeAmount, bool $isOn, StorageEntityWrapperInterface $other): void
    {
        $msg = sprintf(
            _('Die %s von Spieler %s hat 1 Crewman %s deiner Station %s gebeamt. Der Uplink ist %s'),
            $other->getName(),
            $other->getUser()->getName(),
            $foreignCrewChangeAmount > 0 ? 'zu' : 'von',
            $this->spacecraft->getName(),
            $isOn ? 'aktiviert' : 'deaktiviert'
        );

        $this->privateMessageSender->send(
            $other->getUser()->getId(),
            $this->spacecraft->getUser()->getId(),
            $msg,
            PrivateMessageFolderTypeEnum::SPECIAL_STATION,
            $this->spacecraft->getHref()
        );
    }

    // TORPEDOS

    #[Override]
    public function getTorpedo(): ?TorpedoTypeInterface
    {
        return $this->spacecraft->getTorpedo();
    }

    #[Override]
    public function getTorpedoCount(): int
    {
        return $this->spacecraft->getTorpedoCount();
    }

    #[Override]
    public function getMaxTorpedos(): int
    {
        return $this->spacecraft->getMaxTorpedos();
    }

    #[Override]
    public function canTransferTorpedos(InformationInterface $information): bool
    {
        if (!$this->spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)) {
            $information->addInformation("Das Torpedolager ist zerstört");
            return false;
        }

        return true;
    }

    #[Override]
    public function canStoreTorpedoType(TorpedoTypeInterface $torpedoType, InformationInterface $information): bool
    {
        if (
            !$this->spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)
            && $this->spacecraft->getRump()->getTorpedoLevel() !== $torpedoType->getLevel()
        ) {
            $information->addInformationf('Die %s kann den Torpedotyp nicht ausrüsten', $this->spacecraft->getName());
            return false;
        }

        if (
            !$this->spacecraft->hasShipSystem(SpacecraftSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)
            && $torpedoType->getLevel() > $this->spacecraft->getRump()->getTorpedoLevel()
        ) {
            $information->addInformationf("Die %s kann den Torpedotyp nicht ausrüsten", $this->spacecraft->getName());
            return false;
        }

        if (
            $this->spacecraft->getTorpedo() !== null
            && $this->spacecraft->getTorpedo() !== $torpedoType
        ) {
            $information->addInformation("Es ist bereits ein anderer Torpedotyp geladen");
            return false;
        }

        return true;
    }

    #[Override]
    public function changeTorpedo(int $changeAmount, TorpedoTypeInterface $type): void
    {
        $this->shipTorpedoManager->changeTorpedo(
            $this->spacecraftWrapper,
            $changeAmount,
            $type
        );
    }
}
