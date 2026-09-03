<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeletePmCategory;

interface DeletePmCategoryRequestInterface
{
    public function getCategoryId(): int;
}
