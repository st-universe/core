<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteAllPms;

interface DeleteAllPmsRequestInterface
{
    public function getCategoryId(): int;
}
