<?php

namespace Stu\Module\Communication\Action\EditPmCategory;

interface EditPmCategoryRequestInterface
{
    public function getCategoryId(): int;

    public function getName(): string;
}