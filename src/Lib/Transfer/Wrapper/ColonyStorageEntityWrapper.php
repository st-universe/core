<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Wrapper;

use Override;
use request;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\CommodityTransferInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\TorpedoType;
use Stu\Orm\Entity\User;

class ColonyStorageEntityWrapper implements StorageEntityWrapperInterface
{
    public function __construct(
        private ColonyLibFactoryInterface $colonyLibFactory,
        private CommodityTransferInterface $commodityTransfer,
        private StorageManagerInterface $storageManager,
        private TroopTransferUtilityInterface $troopTransferUtility,
        private Colony $colony
    ) {}

    // GENERAL
    #[Override]
    public function get(): EntityWithStorageInterface
    {
        return $this->colony;
    }

    #[Override]
    public function getUser(): User
    {
        return $this->colony->getUser();
    }

    #[Override]
    public function getName(): string
    {
        return sprintf('Kolonie %s', $this->colony->getName());
    }

    #[Override]
    public function canTransfer(InformationInterface $information): bool
    {
        if ($this->colony->getWorkers() + $this->colony->getChangeable()->getWorkless() === 0) {
            $information->addInformation("Es lebt niemand auf dieser Kolonie");
            return false;
        }

        return true;
    }

    #[Override]
    public function getLocation(): Location
    {
        return $this->colony->getLocation();
    }

    // COMMODITIES
    public function getBeamFactor(): int
    {
        return $this->colony->getBeamFactor();
    }

    #[Override]
    public function transfer(
        bool $isUnload,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): void {

        $transferTarget = $isUnload ? $target->get() : $this->colony;
        if ($transferTarget->getMaxStorage() <= $transferTarget->getStorageSum()) {
            $information->addInformationf('%s: Der Lagerraum ist voll', $target->getName());
            return;
        }

        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');

        $storage = $isUnload ? $this->colony->getStorage() : $target->get()->getBeamableStorage();
        if ($storage->isEmpty()) {
            $information->addInformation("Keine Waren zum Beamen vorhanden");
            return;
        }
        if (count($commodities) == 0 || $gcount === []) {
            $information->addInformation("Es wurden keine Waren zum Beamen ausgewählt");
            return;
        }

        $informations = new InformationWrapper();
        $informations->addInformationf(
            'Die Kolonie %s hat folgende Waren %s %s gebeamt',
            $this->colony->getName(),
            $isUnload ? 'zur' : 'von der',
            $target->getName()
        );

        foreach ($commodities as $key => $value) {
            $commodityId = (int) $value;

            if (!array_key_exists($key, $gcount)) {
                continue;
            }

            $this->commodityTransfer->transferCommodity(
                $commodityId,
                $gcount[$key],
                $this->colony,
                $isUnload ? $this->colony : $target->get(),
                $transferTarget,
                $informations
            );
        }

        $informationArray = $informations->getInformations();
        if (count($informationArray) > 1) {

            foreach ($informationArray as $info) {
                $information->addInformation($info);
            }
        }
    }

    // CREW
    #[Override]
    public function getMaxTransferrableCrew(bool $isTarget, User $user): int
    {
        return $this->troopTransferUtility->ownCrewOnTarget($user, $this->colony);
    }

    #[Override]
    public function getFreeCrewSpace(User $user): int
    {
        if ($user !== $this->colony->getUser()) {
            return 0;
        }

        return $this->colonyLibFactory
            ->createColonyPopulationCalculator($this->colony)
            ->getFreeAssignmentCount();
    }

    #[Override]
    public function checkCrewStorage(int $amount, bool $isUnload, InformationInterface $information): bool
    {
        return true;
    }

    #[Override]
    public function acceptsCrewFrom(int $amount, User $user, InformationInterface $information): bool
    {
        return $this->colony->getUser() === $user;
    }

    #[Override]
    public function postCrewTransfer(int $foreignCrewChangeAmount, StorageEntityWrapperInterface $other, InformationInterface $information): void
    {
        // nothing to do here
    }

    // TORPEDOS

    #[Override]
    public function getTorpedo(): ?TorpedoType
    {
        return null;
    }

    #[Override]
    public function getTorpedoCount(): int
    {
        return 0;
    }

    #[Override]
    public function getMaxTorpedos(): int
    {
        return $this->colony->getMaxStorage() - $this->colony->getStorageSum();
    }

    #[Override]
    public function canTransferTorpedos(InformationInterface $information): bool
    {
        return true;
    }

    #[Override]
    public function canStoreTorpedoType(TorpedoType $torpedoType, InformationInterface $information): bool
    {
        return true;
    }

    #[Override]
    public function changeTorpedo(int $changeAmount, TorpedoType $type): void
    {
        if ($changeAmount > 0) {
            $this->storageManager->upperStorage(
                $this->colony,
                $type->getCommodity(),
                $changeAmount
            );
        } else {
            $this->storageManager->lowerStorage(
                $this->colony,
                $type->getCommodity(),
                $changeAmount
            );
        }
    }
}
