<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeletePmCategory;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeletePmCategoryRequest implements DeletePmCategoryRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getCategoryId(): int
    {
        return $this->queryParameter('pmcat')->int()->required();
    }
}
