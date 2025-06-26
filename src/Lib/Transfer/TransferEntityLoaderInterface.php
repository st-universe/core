<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Orm\Entity\User;

interface TransferEntityLoaderInterface
{
    public function loadEntity(
        int $id,
        TransferEntityTypeEnum $entityType,
        bool $checkForEntityLock = true,
        ?User $user = null
    ): StorageEntityWrapperInterface;
}
