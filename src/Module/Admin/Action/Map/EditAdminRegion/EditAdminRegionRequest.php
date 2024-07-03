<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditAdminRegion;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditAdminRegionRequest implements EditAdminRegionRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getFieldId(): int
    {
        return $this->queryParameter('field')->int()->required();
    }

    #[Override]
    public function getAdminRegionId(): int
    {
        return $this->queryParameter('adminregion')->int()->required();
    }
}
