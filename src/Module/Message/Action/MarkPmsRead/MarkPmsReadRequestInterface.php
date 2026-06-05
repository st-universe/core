<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\MarkPmsRead;

interface MarkPmsReadRequestInterface
{
    public function getCategoryId(): int;
}
