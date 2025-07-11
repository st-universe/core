<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Override;
use RuntimeException;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class CommodityTransfer implements CommodityTransferInterface
{
    public function __construct(
        private StorageManagerInterface $storageManager,
        private ColonyRepositoryInterface $colonyRepository
    ) {}

    #[Override]
    public function transferCommodity(
        int $commodityId,
        string|int $wantedAmount,
        SpacecraftWrapperInterface|Colony $subject,
        EntityWithStorageInterface $source,
        EntityWithStorageInterface $target,
        InformationInterface $information
    ): bool {

        $sourceStorage =  $source->getStorage()[$commodityId] ?? null;
        if ($sourceStorage === null) {
            return false;
        }

        $commodity = $sourceStorage->getCommodity();
        if (!$commodity->isBeamable($source->getUser(), $target->getUser())) {
            $information->addInformationf(_('%s ist nicht beambar'), $commodity->getName());
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

        $information->addInformationf(
            _('%d %s (Energieverbrauch: %d)'),
            $amount,
            $commodity->getName(),
            $epsUsage
        );

        $this->storageManager->lowerStorage($source, $commodity, $amount);
        $this->storageManager->upperStorage($target, $commodity, $amount);
        $this->consumeEps($epsUsage, $subject);

        return true;
    }

    #[Override]
    public function isDockTransfer(
        EntityWithStorageInterface $source,
        EntityWithStorageInterface $target
    ): bool {
        return ($source instanceof Ship && $source->getDockedTo() === $target)
            || ($target instanceof Ship && $target->getDockedTo() === $source);
    }

    private function getBeamFactor(SpacecraftWrapperInterface|Colony $subject): int
    {
        if ($subject instanceof SpacecraftWrapperInterface) {
            return $subject->get()->getRump()->getBeamFactor();
        }

        return $subject->getBeamFactor();
    }

    private function getAvailableEps(SpacecraftWrapperInterface|Colony $subject): int
    {
        if ($subject instanceof SpacecraftWrapperInterface) {
            $epsSystem = $subject->getEpsSystemData();

            return $epsSystem === null ? 0 : $epsSystem->getEps();
        }

        return $subject->getChangeable()->getEps();
    }

    private function consumeEps(int $epsUsage, SpacecraftWrapperInterface|Colony $subject): void
    {
        if ($epsUsage === 0) {
            return;
        }

        if ($subject instanceof SpacecraftWrapperInterface) {
            $epsSystem = $subject->getEpsSystemData();
            if ($epsSystem === null) {
                throw new RuntimeException('this should not happen');
            }
            $epsSystem->lowerEps($epsUsage)->update();
        } else {
            $subject->getChangeable()->lowerEps($epsUsage);
            $this->colonyRepository->save($subject);
        }
    }

    /** 
     * @param ArrayCollection<int, Storage> $storage
     * 
     * @return ArrayCollection<int, Storage> sorted by commodity->sort
     */
    public static function excludeNonBeamable(Collection $storage): Collection
    {
        $beamableStorage = $storage
            ->filter(fn(Storage $storage): bool => $storage->getCommodity()->isBeamable() === true)
            ->toArray();

        usort($beamableStorage, function ($a, $b): int {
            return $a->getCommodity()->getSort() <=> $b->getCommodity()->getSort();
        });

        return new ArrayCollection($beamableStorage);
    }
}
