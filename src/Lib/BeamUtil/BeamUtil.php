<?php

declare(strict_types=1);

namespace Stu\Lib\BeamUtil;

use RuntimeException;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class BeamUtil implements BeamUtilInterface
{
    private ShipStorageManagerInterface $shipStorageManager;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    public function __construct(
        ShipStorageManagerInterface $shipStorageManager,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->shipStorageManager = $shipStorageManager;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
    }

    public function transferCommodity(
        int $commodityId,
        string|int $wantedAmount,
        ShipWrapperInterface|ColonyInterface $subject,
        ShipInterface|ColonyInterface $source,
        ShipInterface|ColonyInterface $target,
        GameControllerInterface $game
    ): void {

        $sourceStorage =  $source->getStorage()[$commodityId] ?? null;
        if ($sourceStorage === null) {
            return;
        }

        $commodity = $sourceStorage->getCommodity();
        if (!$commodity->isBeamable($source->getUser(), $target->getUser())) {
            $game->addInformationf(_('%s ist nicht beambar'), $commodity->getName());
            return;
        }

        $isDockTransfer = $source instanceof ShipInterface && $target instanceof ShipInterface
            && ($source->getDockedTo() === $target || $target->getDockedTo() === $source);

        $availableEps = $this->getAvailableEps($subject);
        if (!$isDockTransfer && $availableEps < 1) {
            return;
        }

        if ($wantedAmount === "max") {
            $amount = $sourceStorage->getAmount();
        } else if (!is_numeric($wantedAmount)) {
            return;
        } else {
            $amount =  (int)$wantedAmount;
        }

        if ($amount < 1) {
            return;
        }

        if ($target->getStorageSum() >= $target->getMaxStorage()) {
            return;
        }

        $amount = min($amount, $sourceStorage->getAmount());
        $transferAmount = $commodity->getTransferCount() * $this->getBeamFactor($subject);

        if (!$isDockTransfer && ceil($amount / $transferAmount) > $availableEps) {
            $amount = $availableEps * $transferAmount;
        }

        if ($target->getStorageSum() + $amount > $target->getMaxStorage()) {
            $amount = $target->getMaxStorage() - $target->getStorageSum();
        }

        $epsUsage = (int)ceil($amount / $transferAmount);

        $game->addInformationf(
            _('%d %s (Energieverbrauch: %d)'),
            $amount,
            $commodity->getName(),
            $epsUsage
        );

        $this->lowerSourceStorage($amount, $commodity, $source);
        $this->upperTargetStorage($amount, $commodity, $target);
        $this->consumeEps($epsUsage, $subject);
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
            $this->shipStorageManager->lowerStorage($source, $commodity,  $amount);
        } else {
            $this->colonyStorageManager->lowerStorage($source, $commodity,  $amount);
        }
    }

    private function upperTargetStorage(
        int $amount,
        CommodityInterface $commodity,
        ShipInterface|ColonyInterface $target
    ): void {
        if ($target instanceof ShipInterface) {
            $this->shipStorageManager->upperStorage($target, $commodity,  $amount);
        } else {
            $this->colonyStorageManager->upperStorage($target, $commodity,  $amount);
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
