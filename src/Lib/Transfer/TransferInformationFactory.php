<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Override;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;

class TransferInformationFactory implements TransferInformationFactoryInterface
{
    public function __construct(
        private TransferEntityLoaderInterface $transferEntityLoader,
        private PlayerRelationDeterminatorInterface $playerRelationDeterminator
    ) {}

    #[Override]
    public function createTransferInformation(
        int $sourceId,
        TransferEntityTypeEnum $sourceType,
        int $targetId,
        TransferEntityTypeEnum $targetType,
        TransferTypeEnum $currentType,
        bool $isUnload
    ): TransferInformation {

        $source = $this->transferEntityLoader->loadEntity($sourceId, $sourceType);
        $target = $this->transferEntityLoader->loadEntity($targetId, $targetType);
        $isFriend = $this->playerRelationDeterminator->isFriend($source->getUser(), $target->getUser());

        return new TransferInformation(
            $currentType,
            $source,
            $target,
            $isUnload,
            $isFriend
        );
    }
}
