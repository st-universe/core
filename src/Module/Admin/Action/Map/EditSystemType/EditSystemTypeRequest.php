<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditSystemType;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditSystemTypeRequest implements EditSystemTypeRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getFieldId(): int
    {
        return $this->parameter('field')->int()->required();
    }

    #[\Override]
    public function getSystemType(): int
    {
        return $this->parameter('type')->int()->required();
    }
}
