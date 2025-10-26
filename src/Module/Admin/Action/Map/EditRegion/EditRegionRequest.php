<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditRegion;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditRegionRequest implements EditRegionRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getFieldId(): int
    {
        return $this->parameter('field')->int()->required();
    }

    #[\Override]
    public function getRegionId(): int
    {
        return $this->parameter('region')->int()->required();
    }
}
