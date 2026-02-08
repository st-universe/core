<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditInfluenceArea;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditInfluenceAreaRequest implements EditInfluenceAreaRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getFieldId(): int
    {
        return $this->parameter('field')->int()->required();
    }

    #[\Override]
    public function getInfluenceAreaId(): int
    {
        return $this->parameter('area')->int()->required();
    }
}
