<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Override;
use RuntimeException;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class BeamUtil implements BeamUtilInterface
{
    public function __construct(private ShipStorageManagerInterface $shipStorageManager, private ColonyStorageManagerInterface $colonyStorageManager, private ColonyRepositoryInterface $colonyRepository) {}

    #[Override]
    public function transferCommodity(
        int $commodityId,
        string|int $wantedAmount,
        ShipWrapperInterface|ColonyInterface $subject,
        ShipInterface|ColonyInterface $source,
        ShipInterface|ColonyInterface $target,
        InformationWrapper $informations
    ): bool {

        $sourceStorage =  $source->getStorage()[$commodityId] ?? null;
        if ($sourceStorage === null) {
            return false;
        }

        $commodity = $sourceStorage->getCommodity();
        if (!$commodity->isBeamable($source->getUser(), $target->getUser())) {
            $informations->addInformationf(_('%s ist nicht beambar'), $commodity->getName());
            return false;
        }

        $isDockTransfer = $this->isDockTransfer($source, $target);

        $availableEps = $this->getAvailableEps($subject);
        if (!$isDockTransfer && $availableEps < 1) {
            return false;
        }

        if ($wantedAmount === "max") {
            $amount = $sourceStorage->getAmount();
        } elseif (!is_numeric($wantedAmount)) {
            return false;
        } else {
            $amount =  (int)$wantedAmount;
        }

        if ($amount < 1) {
            return false;
        }

        if ($target->getStorageSum() >= $target->getMaxStorage()) {
            return false;
        }

        $amount = min($amount, $sourceStorage->getAmount());
        $transferAmount = $commodity->getTransferCount() * $this->getBeamFactor($subject);

        if (!$isDockTransfer && ceil($amount / $transferAmount) > $availableEps) {
            $amount = $availableEps * $transferAmount;
        }

        if ($target->getStorageSum() + $amount > $target->getMaxStorage()) {
            $amount = $target->getMaxStorage() - $target->getStorageSum();
        }

        $epsUsage = $isDockTransfer ? 0 : (int)ceil($amount / $transferAmount);

        $informations->addInformationf(
            _('%d %s (Energieverbrauch: %d)'),
            $amount,
            $commodity->getName(),
            $epsUsage
        );

        $this->lowerSourceStorage($amount, $commodity, $source);
        $this->upperTargetStorage($amount, $commodity, $target);
        $this->consumeEps($epsUsage, $subject);

        return true;
    }

    #[Override]
    public function isDockTransfer(
        ShipInterface|ColonyInterface $source,
        ShipInterface|ColonyInterface $target
    ): bool {
        return $source instanceof ShipInterface && $target instanceof ShipInterface
            && ($source->getDockedTo() === $target || $target->getDockedTo() === $source);
    }

    private function getBeamFactor(ShipWrapperInterface|ColonyInterface $subject): int
    {
        if ($subject instanceof ShipWrapperInterface) {
            return $subject->get()->getBeamFactor();
        }

        return $subject->getBeamFactor();
    }

    private function getAvailableEps(ShipWrapperInterface|ColonyInterface $subject): int
    {
        if ($subject instanceof ShipWrapperInterface) {
            $epsSystem = $subject->getEpsSystemData();

            return $epsSystem === null ? 0 : $epsSystem->getEps();
        }

        return $subject->getEps();
    }

    private function lowerSourceStorage(
        int $amount,
        CommodityInterface $commodity,
        ShipInterface|ColonyInterface $source
    ): void {
        if ($source instanceof ShipInterface) {
            $this->shipStorageManager->lowerStorage($source, $commodity, $amount);
        } else {
            $this->colonyStorageManager->lowerStorage($source, $commodity, $amount);
        }
    }

    private function upperTargetStorage(
        int $amount,
        CommodityInterface $commodity,
        ShipInterface|ColonyInterface $target
    ): void {
        if ($target instanceof ShipInterface) {
            $this->shipStorageManager->upperStorage($target, $commodity, $amount);
        } else {
            $this->colonyStorageManager->upperStorage($target, $commodity, $amount);
        }
    }

    private function consumeEps(int $epsUsage, ShipWrapperInterface|ColonyInterface $subject): void
    {
        if ($epsUsage === 0) {
            return;
        }

        if ($subject instanceof ShipWrapperInterface) {
            $epsSystem = $subject->getEpsSystemData();
            if ($epsSystem === null) {
                throw new RuntimeException('this should not happen');
            }
            $epsSystem->lowerEps($epsUsage)->update();
        } else {
            $subject->lowerEps($epsUsage);
            $this->colonyRepository->save($subject);
        }
    }
}