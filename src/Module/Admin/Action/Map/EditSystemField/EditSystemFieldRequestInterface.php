<?php

namespace Stu\Module\Admin\Action\Map\EditSystemField;

interface EditSystemFieldRequestInterface
{
    public function getFieldId(): int;

    public function getFieldType(): int;
}
