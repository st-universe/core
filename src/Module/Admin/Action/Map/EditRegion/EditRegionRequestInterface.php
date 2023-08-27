<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditRegion;

interface EditRegionRequestInterface
{
    public function getFieldId(): int;

    public function getRegionId(): int;
}
