<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\AddPmCategory;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class AddPmCategoryRequest implements AddPmCategoryRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getName(): string
    {
        return $this->tidyString($this->queryParameter('catname')->string()->defaultsToIfEmpty(''));
    }
}
