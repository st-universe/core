<?php

namespace Stu\Module\Ship\Lib\Destruction\Handler;

use Override;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestroyerInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

class TradepostDestruction implements ShipDestructionHandlerInterface
{
    public function __construct(
        private StorageRepositoryInterface $storageRepository,
        private TradePostRepositoryInterface $tradePostRepository,
        private ShipStorageManagerInterface $shipStorageManager
    ) {
    }

    #[Override]
    public function handleShipDestruction(
        ?ShipDestroyerInterface $destroyer,
        ShipWrapperInterface $destroyedShipWrapper,
        ShipDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $tradePost = $destroyedShipWrapper->get()->getTradePost();

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
            $this->shipStorageManager->upperStorage(
                $tradePost->getShip(),
                $storage->getCommodity(),
                $amount
            );

            $this->storageRepository->delete($storage);
        }

        //remove tradepost and cascading stuff
        $this->tradePostRepository->delete($tradePost);
    }
}
