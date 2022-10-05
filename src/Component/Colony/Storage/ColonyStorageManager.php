<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Storage;

use Stu\Component\Colony\Storage\Exception\CommodityMissingException;
use Stu\Component\Colony\Storage\Exception\QuantityTooSmallException;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\ColonyStorageRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class ColonyStorageManager implements ColonyStorageManagerInterface
{
    private ColonyStorageRepositoryInterface $colonyStorageRepository;

    private StorageRepositoryInterface $storageRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ColonyStorageRepositoryInterface $colonyStorageRepository,
        StorageRepositoryInterface $storageRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->colonyStorageRepository = $colonyStorageRepository;
        $this->storageRepository = $storageRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function lowerStorage(ColonyInterface $colony, CommodityInterface $commodity, int $amount): void
    {
        $storage = $colony->getStorage();
        $storageNew = $colony->getStorageNew();

        $stor = $storage[$commodity->getId()] ?? null;
        $storNew = $storageNew[$commodity->getId()] ?? null;
        if ($stor === null) {
            throw new CommodityMissingException();
        }
        if ($storNew === null) {
            throw new CommodityMissingException();
        }

        $storedAmount = $stor->getAmount();
        $storedAmountNew = $storNew->getAmount();

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
        if ($storedAmountNew < $amount) {
            throw new QuantityTooSmallException(
                sprintf(
                    _('Tried to lower commodityId %d (%s) on colonyId %d by %d, but only %d stored.'),
                    $commodity->getId(),
                    $commodity->getName(),
                    $colony->getId(),
                    $amount,
                    $storedAmountNew
                )
            );
        }

        $colony->clearCache();

        if ($storedAmountNew === $amount) {
            $storage->removeElement($stor);
            $storageNew->removeElement($storNew);

            $this->colonyStorageRepository->delete($stor);
            $this->storageRepository->delete($storNew);

            return;
        }
        $stor->setAmount($storedAmount - $amount);
        $storNew->setAmount($storedAmountNew - $amount);

        $this->colonyStorageRepository->save($stor);
        $this->storageRepository->save($storNew);
    }

    public function upperStorage(ColonyInterface $colony, CommodityInterface $commodity, int $amount): void
    {
        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        $storage = $colony->getStorage();
        $storageNew = $colony->getStorageNew();
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\t\t\t\tgetSto, seconds: %F", $endTime - $startTime));
        }
        $commodityId = $commodity->getId();

        $stor = $storage[$commodityId] ?? null;
        $storNew = $storageNew[$commodityId] ?? null;

        if ($stor === null) {
            $stor = $this->colonyStorageRepository->prototype()
                ->setColony($colony)
                ->setCommodity($commodity);

            $storage->set($commodityId, $stor);
        }
        if ($storNew === null) {
            $storNew = $this->storageRepository->prototype()
                ->setUserId($colony->getUser()->getId())
                ->setColony($colony)
                ->setCommodity($commodity);

            $storageNew->set($commodityId, $storNew);
        }
        $stor->setAmount($stor->getAmount() + $amount);
        $storNew->setAmount($storNew->getAmount() + $amount);

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        $this->colonyStorageRepository->save($stor);
        $this->storageRepository->save($storNew);
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
