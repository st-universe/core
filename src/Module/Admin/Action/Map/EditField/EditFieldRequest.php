<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditField;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditFieldRequest implements EditFieldRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getFieldId(): int
    {
        return $this->parameter('field')->int()->required();
    }

    #[Override]
    public function getFieldType(): int
    {
        return $this->parameter('type')->int()->required();
    }
}
