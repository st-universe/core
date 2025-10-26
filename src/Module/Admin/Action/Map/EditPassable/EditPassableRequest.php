<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditPassable;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditPassableRequest implements EditPassableRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getFieldId(): int
    {
        return $this->parameter('field')->int()->required();
    }

    #[\Override]
    public function getPassable(): int
    {
        return $this->parameter('passable')->int()->required();
    }
}
