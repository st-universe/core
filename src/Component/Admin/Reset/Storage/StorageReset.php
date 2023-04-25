<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Storage;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;

final class StorageReset implements StorageResetInterface
{
    private TradeOfferRepositoryInterface $tradeOfferRepository;

    private TorpedoStorageRepositoryInterface $torpedoStorageRepository;

    private StorageRepositoryInterface $storageRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        TradeOfferRepositoryInterface $tradeOfferRepository,
        TorpedoStorageRepositoryInterface $torpedoStorageRepository,
        StorageRepositoryInterface $storageRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->torpedoStorageRepository = $torpedoStorageRepository;
        $this->storageRepository = $storageRepository;
        $this->entityManager = $entityManager;
    }

    public function deleteAllTradeOffers(): void
    {
        echo "  - delete all trade offers\n";

        $this->tradeOfferRepository->truncateAllTradeOffers();

        $this->entityManager->flush();
    }

    public function deleteAllTorpedoStorages(): void
    {
        echo "  - delete all torpedo storages\n";

        $this->torpedoStorageRepository->truncateAllTorpedoStorages();

        $this->entityManager->flush();
    }

    public function deleteAllStorages(): void
    {
        echo "  - deleting all storages\n";

        $this->storageRepository->truncateAllStorages();

        $this->entityManager->flush();
    }
}
