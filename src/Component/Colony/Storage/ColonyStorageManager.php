<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Storage;

use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\ColonyStorageRepositoryInterface;

final class ColonyStorageManager implements ColonyStorageManagerInterface
{
    private ColonyStorageRepositoryInterface $colonyStorageRepository;

    public function __construct(
        ColonyStorageRepositoryInterface $colonyStorageRepository,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->colonyStorageRepository = $colonyStorageRepository;
        $this->loggerUtil = $loggerUtil;
        $this->loggerUtil->init();
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
        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        $storage = $colony->getStorage();
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\t\t\t\tgetSto, seconds: %F", $endTime - $startTime));
        }
        $commodityId = $commodity->getId();

        $stor = $storage[$commodityId] ?? null;

        if ($stor === null) {
            $stor = $this->colonyStorageRepository->prototype()
                ->setColony($colony)
                ->setCommodity($commodity);

            $storage->set($commodityId, $stor);
        }
        $stor->setAmount($stor->getAmount() + $amount);

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        $this->colonyStorageRepository->save($stor);
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\t\t\t\tsave, seconds: %F", $endTime - $startTime));
        }

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        $colony->clearCache();
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\t\t\t\tclearCache, seconds: %F", $endTime - $startTime));
        }
    }
}
