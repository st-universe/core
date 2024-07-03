<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditSystemType;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditSystemTypeRequest implements EditSystemTypeRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getFieldId(): int
    {
        return $this->queryParameter('field')->int()->required();
    }

    #[Override]
    public function getSystemType(): int
    {
        return $this->queryParameter('type')->int()->required();
    }
}
