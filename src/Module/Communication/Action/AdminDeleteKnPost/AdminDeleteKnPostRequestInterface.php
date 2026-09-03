<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AdminDeleteKnPost;

interface AdminDeleteKnPostRequestInterface
{
    public function getKnId(): int;
    public function getReason(): string;
}
