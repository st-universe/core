<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Storage;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\ColonyStorageRepositoryInterface;

final class ColonyStorageManager implements ColonyStorageManagerInterface
{
    private ColonyStorageRepositoryInterface $colonyStorageRepository;

    public function __construct(
        ColonyStorageRepositoryInterface $colonyStorageRepository
    ) {
        $this->colonyStorageRepository = $colonyStorageRepository;
    }

    public function lowerStorage(ColonyInterface $colony, CommodityInterface $commodity, int $amount): void
    {
        $storage = $colony->getStorage();

        $stor = $storage[$commodity->getId()] ?? null;
        if ($stor === null) {
            throw new Exception\CommodityMissingException();
        }

        $storedAmount = $stor->getAmount();

        if ($storedAmount < $amount) {
            throw new Exception\QuantityTooSmallException();
        }

        $colony->clearCache();

        if ($storedAmount === $amount) {
            $storage->removeElement($stor);

            $this->colonyStorageRepository->delete($stor);

            return;
        }
        $stor->setAmount($storedAmount - $amount);

        $this->colonyStorageRepository->save($stor);
    }

    public function upperStorage(ColonyInterface $colony, CommodityInterface $commodity, int $amount): void
    {
        $storage = $colony->getStorage();
        $commodityId = $commodity->getId();

        $stor = $storage[$commodityId] ?? null;

        if ($stor === null) {
            $stor = $this->colonyStorageRepository->prototype()
                ->setColony($colony)
                ->setGood($commodity);

            $storage->set($commodityId, $stor);
        }
        $stor->setAmount($stor->getAmount() + $amount);

        $this->colonyStorageRepository->save($stor);

        $colony->clearCache();
    }
}
