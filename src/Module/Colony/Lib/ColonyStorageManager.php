<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use ColonyData;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\ColonyStorageRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class ColonyStorageManager implements ColonyStorageManagerInterface
{
    private $colonyStorageRepository;

    private $commodityRepository;

    public function __construct(
        ColonyStorageRepositoryInterface $colonyStorageRepository,
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->colonyStorageRepository = $colonyStorageRepository;
        $this->commodityRepository = $commodityRepository;
    }

    public function lowerStorage(ColonyData $colony, CommodityInterface $commodity, int $amount): void
    {
        $stor = $colony->getStorage()[$commodity->getId()] ?? null;
        if ($stor === null) {
            return;
        }

        $colony->clearCache();

        if ($stor->getAmount() <= $amount) {
            $this->colonyStorageRepository->delete($stor);

            return;
        }
        $stor->setAmount($stor->getAmount() - $amount);

        $this->colonyStorageRepository->save($stor);
    }

    public function upperStorage(ColonyData $colony, CommodityInterface $commodity, int $amount): void
    {
        $stor = $colony->getStorage()[$commodity->getId()] ?? null;

        if ($stor === null) {
            $stor = $this->colonyStorageRepository->prototype();
            $stor->setColonyId((int)$colony->getId());
            $stor->setGood($commodity);
        }
        $stor->setAmount($stor->getAmount() + $amount);

        $this->colonyStorageRepository->save($stor);

        $colony->clearCache();
    }
}