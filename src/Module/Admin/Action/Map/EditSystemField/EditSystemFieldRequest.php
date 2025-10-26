<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditSystemField;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditSystemFieldRequest implements EditSystemFieldRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getFieldId(): int
    {
        return $this->parameter('field')->int()->required();
    }

    #[\Override]
    public function getFieldType(): int
    {
        return $this->parameter('type')->int()->required();
    }
}
