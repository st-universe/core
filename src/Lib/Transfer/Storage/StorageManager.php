<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Storage;

use Override;
use RuntimeException;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Lib\Transfer\Storage\Exception\CommodityMissingException;
use Stu\Lib\Transfer\Storage\Exception\QuantityTooSmallException;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class StorageManager implements StorageManagerInterface
{
    public function __construct(
        private readonly StorageRepositoryInterface $storageRepository
    ) {}

    #[Override]
    public function lowerStorage(EntityWithStorageInterface $entity, Commodity $commodity, int $amount): void
    {
        $storageList = $entity->getStorage();

        $storage = $storageList[$commodity->getId()] ?? null;
        if ($storage === null) {
            throw new CommodityMissingException();
        }

        $storedAmount = $storage->getAmount();
        if ($storedAmount < $amount) {
            throw new QuantityTooSmallException(
                sprintf(
                    _('Tried to lower commodityId %d (%s) on entityId %d (%s) by %d, but only %d stored.'),
                    $commodity->getId(),
                    $commodity->getName(),
                    $entity->getId(),
                    $entity->getTransferEntityType()->value,
                    $amount,
                    $storedAmount
                )
            );
        }

        if ($storedAmount === $amount) {
            $storageList->removeElement($storage);
            $this->storageRepository->delete($storage);

            return;
        }

        $storage->setAmount($storedAmount - $amount);

        $this->storageRepository->save($storage);
    }

    #[Override]
    public function upperStorage(EntityWithStorageInterface $entity, Commodity $commodity, int $amount): void
    {
        $storages = $entity->getStorage();
        $commodityId = $commodity->getId();

        $storage = $storages[$commodityId] ?? null;

        if ($storage === null) {
            $user = $entity->getUser();
            if ($user === null) {
                throw new RuntimeException('this should not happen');
            }
            $storage = $this->storageRepository->prototype()
                ->setUser($user)
                ->setCommodity($commodity);

            $this->setOwner($storage, $entity);

            $storages->set($commodityId, $storage);
        }

        $storage->setAmount($storage->getAmount() + $amount);

        $this->storageRepository->save($storage);
    }

    private function setOwner(Storage $storage, EntityWithStorageInterface $entity): void
    {
        $entityType = $entity->getTransferEntityType();

        match ($entityType) {
            TransferEntityTypeEnum::COLONY,
            TransferEntityTypeEnum::SHIP,
            TransferEntityTypeEnum::STATION => $storage->setEntity($entity),
            default => throw new RuntimeException(sprintf('unsupported setter for type %s', $entityType->value))
        };
    }
}
