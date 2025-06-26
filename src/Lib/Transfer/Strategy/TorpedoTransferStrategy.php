<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Strategy;

use Override;
use request;
use RuntimeException;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Colony;

class TorpedoTransferStrategy implements TransferStrategyInterface
{
    #[Override]
    public function setTemplateVariables(
        bool $isUnload,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        GameControllerInterface $game
    ): void {

        if ($target instanceof Colony) {
            throw new RuntimeException('this should not happen');
        }

        if ($isUnload) {
            $max = min(
                $target->getMaxTorpedos(),
                $source->getTorpedoCount()
            );
            $commodityId = $source->getTorpedo() === null ? null : $source->getTorpedo()->getCommodityId();
        } else {
            $max = $target->getTorpedoCount();
            $commodityId = $target->getTorpedo() === null ? null : $target->getTorpedo()->getCommodityId();
        }

        $game->setTemplateVar('MAXIMUM', $max);
        $game->setTemplateVar('COMMODITY_ID', $commodityId);
    }

    #[Override]
    public function transfer(
        bool $isUnload,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): void {

        $torpedoType = $isUnload ? $source->getTorpedo() : $target->getTorpedo();
        if ($torpedoType === null) {
            throw new RuntimeException('this should not happen');
        }

        if (!$source->canTransferTorpedos($information)) {
            return;
        }

        $destination = $isUnload ? $target : $source;
        if (!$destination->canStoreTorpedoType($torpedoType, $information)) {
            return;
        }

        //TODO use energy to transfer
        $requestedTransferCount = request::postInt('tcount');

        $amount = min(
            $requestedTransferCount,
            $isUnload ? $source->getTorpedoCount() : $target->getTorpedoCount(),
            $isUnload ? $target->getMaxTorpedos() - $target->getTorpedoCount() : $source->getMaxTorpedos() - $source->getTorpedoCount()
        );

        if ($amount < 1) {
            $information->addInformation('Es konnten keine Torpedos transferiert werden');
            return;
        }

        $target->changeTorpedo($isUnload ? $amount : -$amount, $torpedoType);
        $source->changeTorpedo($isUnload ? -$amount : $amount, $torpedoType);

        $information->addInformation(
            sprintf(
                'Die %s hat in Sektor %s %d Torpedos %s %s transferiert',
                $source->getName(),
                $source->getLocation()->getSectorString(),
                $amount,
                $isUnload ? 'zur' : 'von der',
                $target->getName()
            ),
        );
    }
}
