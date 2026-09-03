<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditSystemField;

interface EditSystemFieldRequestInterface
{
    public function getFieldId(): int;

    public function getFieldType(): int;
}
