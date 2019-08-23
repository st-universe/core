<?php

namespace Stu\Module\Starmap\Action\EditSystemField;

interface EditSystemFieldRequestInterface
{
    public function getFieldId(): int;

    public function getFieldType(): int;
}