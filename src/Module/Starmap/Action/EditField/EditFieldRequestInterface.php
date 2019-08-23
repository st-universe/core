<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Action\EditField;

interface EditFieldRequestInterface
{
    public function getFieldId(): int;

    public function getFieldType(): int;
}