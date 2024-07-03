<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditRegion;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditRegionRequest implements EditRegionRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getFieldId(): int
    {
        return $this->queryParameter('field')->int()->required();
    }

    #[Override]
    public function getRegionId(): int
    {
        return $this->queryParameter('region')->int()->required();
    }
}
