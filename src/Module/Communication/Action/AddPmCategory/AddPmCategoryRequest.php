<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddPmCategory;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class AddPmCategoryRequest implements AddPmCategoryRequestInterface
{
    use CustomControllerHelperTrait;

    public function getName(): string
    {
        return tidyString($this->queryParameter('catname')->string()->defaultsToIfEmpty(''));
    }
}