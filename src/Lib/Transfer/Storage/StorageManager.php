<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Storage;

use Doctrine\ORM\EntityManagerInterface;
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
        private readonly StorageRepositoryInterface $storageRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Override]
    public function lowerStorage(EntityWithStorageInterface $entity, Commodity $commodity, int $amount): void
    {
        $this->removeStorage($entity, $commodity, $amount);
    }

    #[Override]
    public function removeStorage(EntityWithStorageInterface $entity, Commodity $commodity, int $amount, ?int $quality = null): array
    {
        $storageList = $entity->getStorage();
        $storage = $storageList[$commodity->getId()] ?? null;
        if ($storage === null) {
            throw new CommodityMissingException();
        }
        $storedAmount = $storage->getAmount();
        if ($amount < 0 || $storedAmount < $amount) {
            throw new QuantityTooSmallException(sprintf(
                'Tried to lower commodityId %d (%s) on entityId %d (%s) by %d, but only %d stored.',
                $commodity->getId(),
                $commodity->getName(),
                $entity->getId(),
                $entity->getTransferEntityType()->value,
                $amount,
                $storedAmount
            ));
        }
        $removed = $storage->removeAmount($amount, $quality);
        if ($storedAmount === $amount) {
            $storageList->removeElement($storage);
            $this->storageRepository->delete($storage);
        } else {
            $this->storageRepository->save($storage);
        }
        return $removed;
    }

    #[Override]
    public function addStorage(EntityWithStorageInterface $entity, Commodity $commodity, array $qualities): void
    {
        $amount = ModuleQualityPacket::validate($qualities);
        if ($amount === 0) {
            return;
        }
        $this->upperStorage($entity, $commodity, $amount);
        $storage = $entity->getStorage()->get($commodity->getId());
        if ($storage === null) {
            throw new RuntimeException('Storage missing after addition');
        }
        $storage->addModuleQualityAmounts($qualities);
    }

    #[Override]
    public function transferStorage(EntityWithStorageInterface $source, EntityWithStorageInterface $target, Commodity $commodity, int $amount, ?int $quality = null): void
    {
        if ($source === $target) {
            return;
        }
        $qualities = $this->removeStorage($source, $commodity, $amount, $quality);
        $this->addStorage($target, $commodity, $qualities);
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
            $managedCommodity = $this->entityManager->getReference(Commodity::class, $commodityId);
            if (!$managedCommodity instanceof Commodity) {
                throw new RuntimeException(sprintf('commodity with id %d could not be referenced', $commodityId));
            }

            $storage = $this->storageRepository->prototype()
                ->setUser($user)
                ->setCommodity($managedCommodity);

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
