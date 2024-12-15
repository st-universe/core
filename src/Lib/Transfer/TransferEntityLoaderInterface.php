<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Orm\Entity\UserInterface;

interface TransferEntityLoaderInterface
{
    public function loadEntity(
        int $id,
        TransferEntityTypeEnum $entityType,
        bool $checkForEntityLock = true,
        ?UserInterface $user = null
    ): StorageEntityWrapperInterface;
}
