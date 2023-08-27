<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditBorder;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditBorderRequest implements EditBorderRequestInterface
{
    use CustomControllerHelperTrait;

    public function getFieldId(): int
    {
        return $this->queryParameter('field')->int()->required();
    }

    public function getBorder(): int
    {
        return $this->queryParameter('border')->int()->required();
    }
}
