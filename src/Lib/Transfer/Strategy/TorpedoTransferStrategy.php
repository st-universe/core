<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Strategy;

use request;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\TorpedoType;

class TorpedoTransferStrategy implements TransferStrategyInterface
{
    #[\Override]
    public function setTemplateVariables(
        bool $isUnload,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        GameControllerInterface $game
    ): void {

        $torpedoSource = $isUnload ? $source : $target;
        $torpedoDestination = $isUnload ? $target : $source;
        $freeCapacity = max(0, $torpedoDestination->getMaxTorpedos() - $torpedoDestination->getTotalTorpedoCount());

        $transfers = [];
        foreach ($torpedoSource->getTorpedoStorages() as $torpedoStorage) {
            $torpedo = $torpedoStorage->getTorpedo();
            $amount = $torpedoStorage->getStorage()->getAmount();
            $maximum = min($amount, $freeCapacity);

            if (
                $amount < 1
                || !$this->canDisplayTorpedoType($torpedoDestination, $torpedo)
            ) {
                continue;
            }

            $transfers[] = [
                'torpedo' => $torpedo,
                'maximum' => $maximum
            ];
        }

        $game->setTemplateVar('TORPEDO_TRANSFERS', $transfers);
    }

    private function canDisplayTorpedoType(
        StorageEntityWrapperInterface $destination,
        TorpedoType $torpedoType
    ): bool {
        $spacecraft = $destination->get();
        if (
            $spacecraft instanceof Spacecraft
            && !$spacecraft->isTorpedoStorageHealthy()
            && $spacecraft->getRump()->getTorpedoLevel() !== $torpedoType->getLevel()
        ) {
            return false;
        }

        return $destination->canStoreTorpedoType($torpedoType);
    }

    #[\Override]
    public function transfer(
        bool $isUnload,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): void {

        if (!$source->canTransferTorpedos($information)) {
            return;
        }

        $destination = $isUnload ? $target : $source;
        $torpedoSource = $isUnload ? $source : $target;
        $storagesByTorpedoType = [];
        foreach ($torpedoSource->getTorpedoStorages() as $torpedoStorage) {
            $storagesByTorpedoType[$torpedoStorage->getTorpedo()->getId()] = $torpedoStorage;
        }

        $wasTransferred = false;
        foreach (request::postArray('tcount') as $torpedoTypeId => $requestedTransferCount) {
            $torpedoStorage = $storagesByTorpedoType[(int) $torpedoTypeId] ?? null;
            if ($torpedoStorage === null) {
                continue;
            }

            $torpedoType = $torpedoStorage->getTorpedo();
            if (!$destination->canStoreTorpedoType($torpedoType, $information)) {
                continue;
            }

            $amount = min(
                max(0, (int) $requestedTransferCount),
                $torpedoStorage->getStorage()->getAmount(),
                $destination->getMaxTorpedos() - $destination->getTotalTorpedoCount()
            );
            if ($amount < 1) {
                continue;
            }

            //TODO use energy to transfer
            $target->changeTorpedo($isUnload ? $amount : -$amount, $torpedoType);
            $source->changeTorpedo($isUnload ? -$amount : $amount, $torpedoType);

            $information->addInformation(
                sprintf(
                    'Die %s hat in Sektor %s %d %s %s %s transferiert',
                    $source->getName(),
                    $source->getLocation()->getSectorString(),
                    $amount,
                    $torpedoType->getName(),
                    $isUnload ? 'zur' : 'von der',
                    $target->getName()
                ),
            );
            $wasTransferred = true;
        }

        if (!$wasTransferred) {
            $information->addInformation('Es konnten keine Torpedos transferiert werden');
        }
    }
}
