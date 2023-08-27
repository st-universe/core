<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditRegion;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditRegionRequest implements EditRegionRequestInterface
{
    use CustomControllerHelperTrait;

    public function getFieldId(): int
    {
        return $this->queryParameter('field')->int()->required();
    }

    public function getRegionId(): int
    {
        return $this->queryParameter('region')->int()->required();
    }
}
