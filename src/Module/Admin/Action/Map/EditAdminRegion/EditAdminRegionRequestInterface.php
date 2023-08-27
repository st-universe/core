<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditAdminRegion;

interface EditAdminRegionRequestInterface
{
    public function getFieldId(): int;

    public function getAdminRegionId(): int;
}
