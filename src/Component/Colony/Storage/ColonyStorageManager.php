<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Storage;

use Override;
use Stu\Component\Colony\Storage\Exception\CommodityMissingException;
use Stu\Component\Colony\Storage\Exception\QuantityTooSmallException;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class ColonyStorageManager implements ColonyStorageManagerInterface
{
    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private StorageRepositoryInterface $storageRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function lowerStorage(ColonyInterface $colony, CommodityInterface $commodity, int $amount): void
    {
        $storage = $colony->getStorage();

        $stor = $storage[$commodity->getId()] ?? null;
        if ($stor === null) {
            throw new CommodityMissingException();
        }

        $storedAmount = $stor->getAmount();

        if ($storedAmount < $amount) {
            throw new QuantityTooSmallException(
                sprintf(
                    _('Tried to lower commodityId %d (%s) on colonyId %d by %d, but only %d stored.'),
                    $commodity->getId(),
                    $commodity->getName(),
                    $colony->getId(),
                    $amount,
                    $storedAmount
                )
            );
        }

        if ($storedAmount === $amount) {
            $storage->removeElement($stor);

            $this->storageRepository->delete($stor);

            return;
        }

        $stor->setAmount($storedAmount - $amount);

        $this->storageRepository->save($stor);
    }

    #[Override]
    public function upperStorage(ColonyInterface $colony, CommodityInterface $commodity, int $amount): void
    {
        $startTime = null;
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
            $stor = $this->storageRepository->prototype()
                ->setUser($colony->getUser())
                ->setColony($colony)
                ->setCommodity($commodity);

            $storage->set($commodityId, $stor);
        }

        $stor->setAmount($stor->getAmount() + $amount);

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }

        $this->storageRepository->save($stor);
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\t\t\t\tsave, seconds: %F", $endTime - $startTime));
        }

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }

        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\t\t\t\tclearCache, seconds: %F", $endTime - $startTime));
        }
    }
}
