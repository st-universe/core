<?php

namespace Stu\Module\Message\Action\EditPmCategory;

interface EditPmCategoryRequestInterface
{
    public function getCategoryId(): int;

    public function getName(): string;
}
