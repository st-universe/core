<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Orm\Entity\User;

class TransferInformationFactory implements TransferInformationFactoryInterface
{
    public function __construct(
        private TransferEntityLoaderInterface $transferEntityLoader,
        private PlayerRelationDeterminatorInterface $playerRelationDeterminator
    ) {}

    #[\Override]
    public function createTransferInformation(
        int $sourceId,
        TransferEntityTypeEnum $sourceType,
        int $targetId,
        TransferEntityTypeEnum $targetType,
        TransferTypeEnum $currentType,
        bool $isUnload,
        User $user,
        bool $checkForEntityLock
    ): TransferInformation {

        $source = $this->transferEntityLoader->loadEntity($sourceId, $sourceType, $checkForEntityLock, $user);
        $target = $this->transferEntityLoader->loadEntity($targetId, $targetType, $checkForEntityLock);
        $isFriend = $this->playerRelationDeterminator->isFriend($target->getUser(), $source->getUser());

        return new TransferInformation(
            $currentType,
            $source,
            $target,
            $isUnload,
            $isFriend
        );
    }
}
