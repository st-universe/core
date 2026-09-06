<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\UpdateContactPermissions;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class UpdateContactPermissionsRequest implements UpdateContactPermissionsRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getContactId(): int
    {
        return $this->parameter('contact_permission_id')->int()->required();
    }

    #[\Override]
    public function getPermissions(): int
    {
        return $this->parameter('contact_permissions')->int()->defaultsTo(0);
    }
}
