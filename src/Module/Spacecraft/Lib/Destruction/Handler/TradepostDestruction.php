<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

class TradepostDestruction implements SpacecraftDestructionHandlerInterface
{
    public function __construct(
        private StorageRepositoryInterface $storageRepository,
        private TradePostRepositoryInterface $tradePostRepository,
        private StorageManagerInterface $storageManager
    ) {}

    #[Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        if (!$destroyedSpacecraftWrapper instanceof StationWrapperInterface) {
            return;
        }

        $tradePost = $destroyedSpacecraftWrapper->get()->getTradePost();
        if ($tradePost === null) {
            return;
        }

        //salvage offers and storage
        $storages = $this->storageRepository->getByTradePost($tradePost->getId());
        foreach ($storages as $storage) {
            //only 50% off all storages
            if (random_int(0, 1) === 0) {
                $this->storageRepository->delete($storage);
                continue;
            }

            //only 0 to 50% of the specific amount
            $amount = (int)ceil($storage->getAmount() / 100 * random_int(0, 50));

            if ($amount === 0) {
                $this->storageRepository->delete($storage);
                continue;
            }

            //add to trumfield storage
            $this->storageManager->upperStorage(
                $tradePost->getStation(),
                $storage->getCommodity(),
                $amount
            );

            $this->storageRepository->delete($storage);
        }

        //remove tradepost and cascading stuff
        $this->tradePostRepository->delete($tradePost);
    }
}
