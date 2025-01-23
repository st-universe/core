<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;

class TransferInformation
{
    private EntityWithStorageInterface $source;
    private EntityWithStorageInterface $target;

    public function __construct(
        private TransferTypeEnum $currentType,
        private StorageEntityWrapperInterface $sourceWrapper,
        private StorageEntityWrapperInterface $targetWrapper,
        private bool $isUnload,
        private bool $isFriend
    ) {
        $this->source = $sourceWrapper->get();
        $this->target = $targetWrapper->get();
    }

    public function isCommodityTransferPossible(bool $isOtherTypeRequired = true): bool
    {
        return !($isOtherTypeRequired
            && $this->currentType === TransferTypeEnum::COMMODITIES);
    }

    public function isCrewTransferPossible(bool $isOtherTypeRequired = true): bool
    {
        if (
            $isOtherTypeRequired
            && $this->currentType === TransferTypeEnum::CREW
        ) {
            return false;
        }

        if (
            $this->target instanceof SpacecraftInterface
            && $this->target->hasUplink()
        ) {
            return true;
        }

        return $this->target->getUser() === $this->source->getUser();
    }

    public function isTorpedoTransferPossible(bool $isOtherTypeRequired = true): bool
    {
        if (
            $isOtherTypeRequired
            && $this->currentType === TransferTypeEnum::TORPEDOS
        ) {
            return false;
        }

        return $this->source instanceof SpacecraftInterface
            && $this->target instanceof SpacecraftInterface
            && $this->source->isTorpedoStorageHealthy()
            && (!$this->isUnload() || $this->source->getTorpedoCount() > 0)
            && ($this->target->hasTorpedo() || $this->target->isTorpedoStorageHealthy())
            && ($this->isUnload() || $this->target->getTorpedoCount() > 0);
    }

    public function isFriend(): bool
    {
        return $this->isFriend || $this->source->getUser() === $this->target->getUser();
    }

    public function isOtherGoodTransferPossible(): bool
    {
        return $this->isCommodityTransferPossible()
            || $this->isCrewTransferPossible()
            || $this->isTorpedoTransferPossible();
    }

    public function getTransferType(): TransferTypeEnum
    {
        return $this->currentType;
    }

    public function getSourceType(): TransferEntityTypeEnum
    {
        return $this->source->getTransferEntityType();
    }

    public function getTargetType(): TransferEntityTypeEnum
    {
        return $this->target->getTransferEntityType();
    }

    public function isUnload(): bool
    {
        return $this->isUnload;
    }

    public function getSourceId(): int
    {
        return $this->source->getId();
    }

    public function getTargetId(): int
    {
        return $this->target->getId();
    }

    public function getSource(): EntityWithStorageInterface
    {
        return $this->source;
    }

    public function getTarget(): EntityWithStorageInterface
    {
        return $this->target;
    }

    public function getSourceWrapper(): StorageEntityWrapperInterface
    {
        return $this->sourceWrapper;
    }

    public function getTargetWrapper(): StorageEntityWrapperInterface
    {
        return $this->targetWrapper;
    }

    public function getBeamFactor(): int
    {
        return $this->sourceWrapper->getBeamFactor();
    }

    public function isSourceAbove(): bool
    {
        return $this->getSourceType() !== TransferEntityTypeEnum::COLONY;
    }

    public function isUpIcon(): bool
    {
        return ($this->getSource() instanceof ColonyInterface) === $this->isUnload();
    }

    public function showFleetleaderActions(): bool
    {
        $source = $this->getSource();
        if (!$source instanceof ShipInterface) {
            return false;
        }

        $fleet = $source->getFleet();

        return $fleet !== null
            && $source->isFleetLeader()
            && $fleet->getShipCount() > 1;
    }
}
