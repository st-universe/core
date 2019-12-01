<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\ColonyStorageRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class ColonyStorageManager implements ColonyStorageManagerInterface
{
    private ColonyStorageRepositoryInterface $colonyStorageRepository;

    private CommodityRepositoryInterface $commodityRepository;

    public function __construct(
        ColonyStorageRepositoryInterface $colonyStorageRepository,
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->colonyStorageRepository = $colonyStorageRepository;
        $this->commodityRepository = $commodityRepository;
    }

    public function lowerStorage(ColonyInterface $colony, CommodityInterface $commodity, int $amount): void
    {
        $stor = $colony->getStorage()[$commodity->getId()] ?? null;
        if ($stor === null) {
            return;
        }

        $colony->clearCache();

        if ($stor->getAmount() <= $amount) {
            $colony->getStorage()->removeElement($stor);

            $this->colonyStorageRepository->delete($stor);

            return;
        }
        $stor->setAmount($stor->getAmount() - $amount);

        $this->colonyStorageRepository->save($stor);
    }

    public function upperStorage(ColonyInterface $colony, CommodityInterface $commodity, int $amount): void
    {
        $stor = $colony->getStorage()[$commodity->getId()] ?? null;

        if ($stor === null) {
            $stor = $this->colonyStorageRepository->prototype();
            $stor->setColony($colony);
            $stor->setGood($commodity);

            $colony->getStorage()->add($stor);
        }
        $stor->setAmount($stor->getAmount() + $amount);

        $this->colonyStorageRepository->save($stor);

        $colony->clearCache();
    }
}
