<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditInfluenceArea;

interface EditInfluenceAreaRequestInterface
{
    public function getFieldId(): int;

    public function getInfluenceAreaId(): int;
}
