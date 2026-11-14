<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class DiscardBorgDataNodes implements SpacecraftDestructionHandlerInterface
{
    public function __construct(private StorageRepositoryInterface $storageRepository) {}

    #[Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {
        $storage = $destroyedSpacecraftWrapper->get()->getStorage();
        foreach ($storage->toArray() as $entry) {
            if ($entry->getCommodity()->getId() === CommodityTypeConstants::COMMODITY_BORG_DATA_NODE) {
                $storage->removeElement($entry);
                $this->storageRepository->delete($entry);
            }
        }
    }
}
