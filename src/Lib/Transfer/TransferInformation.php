<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

class TransferInformation
{
    public function __construct(private TransferTypeEnum $currentType, private ColonyInterface|ShipInterface $from, private ColonyInterface|ShipInterface $to, private bool $isUnload, private bool $isFriend)
    {
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
            $this->to instanceof ShipInterface
            && $this->to->hasUplink()
        ) {
            return true;
        }

        return $this->to->getUser() === $this->from->getUser();
    }

    public function isTorpedoTransferPossible(bool $isOtherTypeRequired = true): bool
    {
        if (
            $isOtherTypeRequired
            && $this->currentType === TransferTypeEnum::TORPEDOS
        ) {
            return false;
        }

        if (
            !$this->isFriend
            && $this->from->getUser() !== $this->to->getUser()
        ) {
            return false;
        }

        return $this->from instanceof ShipInterface
            && $this->to instanceof ShipInterface
            && $this->from->isTorpedoStorageHealthy()
            && (!$this->isUnload() || $this->from->getTorpedoCount() > 0)
            && ($this->to->hasTorpedo() || $this->to->isTorpedoStorageHealthy())
            && ($this->isUnload() || $this->to->getTorpedoCount() > 0);
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

    public function isUnload(): bool
    {
        return $this->isUnload;
    }

    public function getTargetId(): int
    {
        return $this->to->getId();
    }

    public function isColonyTarget(): bool
    {
        return $this->to instanceof ColonyInterface;
    }

    public function getSource(): ShipInterface|ColonyInterface
    {
        return $this->from;
    }

    public function getTarget(): ShipInterface|ColonyInterface
    {
        return $this->to;
    }
}
