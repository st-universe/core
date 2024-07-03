<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Storage;

use Override;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;

final class StorageReset implements StorageResetInterface
{
    public function __construct(private TradeOfferRepositoryInterface $tradeOfferRepository, private TorpedoStorageRepositoryInterface $torpedoStorageRepository, private StorageRepositoryInterface $storageRepository, private EntityManagerInterface $entityManager)
    {
    }

    #[Override]
    public function deleteAllTradeOffers(): void
    {
        echo "  - delete all trade offers\n";

        $this->tradeOfferRepository->truncateAllTradeOffers();

        $this->entityManager->flush();
    }

    #[Override]
    public function deleteAllTorpedoStorages(): void
    {
        echo "  - delete all torpedo storages\n";

        $this->torpedoStorageRepository->truncateAllTorpedoStorages();

        $this->entityManager->flush();
    }

    #[Override]
    public function deleteAllStorages(): void
    {
        echo "  - deleting all storages\n";

        $this->storageRepository->truncateAllStorages();

        $this->entityManager->flush();
    }
}
