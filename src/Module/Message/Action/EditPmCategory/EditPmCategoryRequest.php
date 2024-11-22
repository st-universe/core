<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\EditPmCategory;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditPmCategoryRequest implements EditPmCategoryRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getCategoryId(): int
    {
        return $this->parameter('pmcat')->int()->required();
    }

    #[Override]
    public function getName(): string
    {
        return $this->tidyString($this->parameter('catname')->string()->defaultsToIfEmpty(''));
    }
}
