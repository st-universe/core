<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeletePmCategory;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeletePmCategoryRequest implements DeletePmCategoryRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getCategoryId(): int
    {
        return $this->parameter('pmcat')->int()->required();
    }
}
