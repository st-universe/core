<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditBorder;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditBorderRequest implements EditBorderRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getFieldId(): int
    {
        return $this->queryParameter('field')->int()->required();
    }

    #[Override]
    public function getBorder(): int
    {
        return $this->queryParameter('border')->int()->required();
    }
}
