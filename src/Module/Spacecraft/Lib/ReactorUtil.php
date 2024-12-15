<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Doctrine\Common\Collections\Collection;
use Override;
use RuntimeException;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

//TODO create unit test
final class ReactorUtil implements ReactorUtilInterface
{
    public function __construct(
        private StorageManagerInterface $storageManager,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function storageContainsNeededCommodities(Collection $storages, ReactorWrapperInterface $reactor): bool
    {
        foreach ($reactor->get()->getLoadCost() as $commodityId => $loadCost) {
            $storage = $storages->get($commodityId);

            if ($storage === null) {
                return false;
            }
            if ($storage->getAmount() < $loadCost) {
                return false;
            }
        }

        return true;
    }

    #[Override]
    public function loadReactor(
        SpacecraftInterface $spacecraft,
        int $additionalLoad,
        ?ManagerProviderInterface $managerProvider,
        ReactorWrapperInterface $reactor
    ): ?string {
        if ($reactor->getLoad() >= $reactor->getCapacity()) {
            return null;
        }

        $capaPerLoad = $reactor->get()->getLoadUnits();

        //check for core limitation
        $loadUnits = ceil($additionalLoad /  $capaPerLoad);
        if ($loadUnits *  $capaPerLoad > $reactor->getCapacity() - $reactor->getLoad()) {
            $loadUnits = ceil(($reactor->getCapacity() - $reactor->getLoad()) /  $capaPerLoad);
        }

        $loadUnits = (int) $loadUnits;

        if ($loadUnits < 1) {
            return null;
        }

        $storage = $managerProvider !== null ? $managerProvider->getStorage() : $spacecraft->getStorage();

        // check for ressource limitation
        $costs = $reactor->get()->getLoadCost();
        foreach ($costs as $commodityId => $loadUnitsCost) {
            $storageElement = $storage->get($commodityId);

            if ($storageElement === null) {
                throw new RuntimeException('storageContainsNeededCommodities should be called first');
            }

            if ($storageElement->getAmount() < ($loadUnits * $loadUnitsCost)) {
                $loadUnits = (int) ($storageElement->getAmount() / $loadUnitsCost);
            }
        }

        //consume ressources
        foreach ($costs as $commodityId => $loadCost) {
            $storageElement = $storage->get($commodityId);

            if ($storageElement === null) {
                throw new RuntimeException('storageContainsNeededCommodities should be called first');
            }

            if ($managerProvider !== null) {
                $managerProvider->lowerStorage(
                    $storageElement->getCommodity(),
                    $loadCost * $loadUnits
                );
            } else {
                $this->storageManager->lowerStorage(
                    $spacecraft,
                    $storageElement->getCommodity(),
                    $loadCost * $loadUnits
                );
            }
        }

        //truncate output
        if ($reactor->getLoad() + $loadUnits *  $capaPerLoad > $reactor->getCapacity()) {
            $loadUnits = $reactor->getCapacity() - $reactor->getLoad();
        } else {
            $loadUnits *= $capaPerLoad;
        }
        $reactor->changeLoad($loadUnits);
        $this->spacecraftRepository->save($spacecraft);

        $systemName = $reactor->get()->getSystemType()->getDescription();

        if ($managerProvider !== null) {
            $this->privateMessageSender->send(
                $managerProvider->getUser()->getId(),
                $spacecraft->getUser()->getId(),
                sprintf(
                    _('Die %s hat in Sektor %s den %s der %s um %d Einheiten aufgeladen'),
                    $managerProvider->getName(),
                    $spacecraft->getSectorString(),
                    $systemName,
                    $spacecraft->getName(),
                    $loadUnits
                ),
                PrivateMessageFolderTypeEnum::SPECIAL_TRADE,
                $spacecraft->getHref()
            );
        }

        return sprintf(
            _('%s: Der %s wurde um %d Einheiten aufgeladen'),
            $spacecraft->getName(),
            $systemName,
            $loadUnits
        );
    }
}
