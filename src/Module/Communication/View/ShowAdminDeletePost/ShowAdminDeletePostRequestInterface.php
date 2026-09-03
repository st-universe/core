<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowAdminDeletePost;

interface ShowAdminDeletePostRequestInterface
{
    public function getPostId(): int;
}
