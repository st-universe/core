<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\EditPmCategory;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditPmCategoryRequest implements EditPmCategoryRequestInterface
{
    use CustomControllerHelperTrait;

    public function getCategoryId(): int
    {
        return $this->queryParameter('pmcat')->int()->required();
    }

    public function getName(): string
    {
        return $this->tidyString($this->queryParameter('catname')->string()->defaultsToIfEmpty(''));
    }
}
