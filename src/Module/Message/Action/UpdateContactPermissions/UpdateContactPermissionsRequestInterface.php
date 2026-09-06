<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\UpdateContactPermissions;

interface UpdateContactPermissionsRequestInterface
{
    public function getContactId(): int;

    public function getPermissions(): int;
}
