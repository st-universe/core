<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditPassable;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditPassableRequest implements EditPassableRequestInterface
{
    use CustomControllerHelperTrait;

    public function getFieldId(): int
    {
        return $this->queryParameter('field')->int()->required();
    }

    public function getPassable(): int
    {
        return $this->queryParameter('passable')->int()->required();
    }
}
