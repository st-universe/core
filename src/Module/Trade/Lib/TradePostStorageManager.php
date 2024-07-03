<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Override;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class TradePostStorageManager implements TradePostStorageManagerInterface
{
    private ?int $storageSum = null;

    /**
     * @var ArrayCollection<int, StorageInterface>
     */
    private ?ArrayCollection $storage = null;

    public function __construct(private StorageRepositoryInterface $storageRepository, private CommodityRepositoryInterface $commodityRepository, private TradePostInterface $tradePost, private UserInterface $user)
    {
    }

    #[Override]
    public function getTradePost(): TradePostInterface
    {
        return $this->tradePost;
    }

    #[Override]
    public function getStorageSum(): int
    {
        if ($this->storageSum === null) {
            $this->storageSum = array_reduce(
                $this->getStorage()->toArray(),
                fn (int $value, StorageInterface $storage): int => $value + $storage->getAmount(),
                0
            );
        }
        return $this->storageSum;
    }

    #[Override]
    public function getFreeStorage(): int
    {
        return max(0, $this->tradePost->getStorage() - $this->getStorageSum());
    }

    #[Override]
    public function getStorage(): Collection
    {
        if ($this->storage === null) {
            $this->storage = new ArrayCollection();

            foreach ($this->storageRepository->getByTradePostAndUser($this->tradePost->getId(), $this->user->getId()) as $storage) {
                $this->storage->set($storage->getCommodityId(), $storage);
            }
        }

        return $this->storage;
    }

    #[Override]
    public function upperStorage(int $commodityId, int $amount): void
    {
        $storage = $this->getStorage();

        $stor = $storage->get($commodityId) ?? null;
        if ($stor === null) {
            $stor = $this->storageRepository->prototype();
            $stor->setUser($this->user);
            $commodity = $this->commodityRepository->find($commodityId);
            if ($commodity === null) {
                throw new InvalidArgumentException(sprintf('commodityId %d does not exist', $commodityId));
            }
            $stor->setCommodity($commodity);
            $stor->setTradePost($this->tradePost);
        }
        $stor->setAmount($stor->getAmount() + $amount);
        $storage->set($commodityId, $stor);

        $this->storageRepository->save($stor);
    }

    #[Override]
    public function lowerStorage(int $commodityId, int $amount): void
    {
        $storage = $this->getStorage();

        $stor = $storage->get($commodityId) ?? null;
        if ($stor === null) {
            return;
        }

        if ($stor->getAmount() <= $amount) {
            $storage->remove($commodityId);
            $this->storageRepository->delete($stor);
            return;
        }
        $stor->setAmount($stor->getAmount() - $amount);

        $this->storageRepository->save($stor);
    }
}
