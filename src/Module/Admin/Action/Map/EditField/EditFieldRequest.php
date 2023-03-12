<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditField;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditFieldRequest implements EditFieldRequestInterface
{
    use CustomControllerHelperTrait;

    public function getFieldId(): int
    {
        return $this->queryParameter('field')->int()->required();
    }

    public function getFieldType(): int
    {
        return $this->queryParameter('type')->int()->required();
    }
}
