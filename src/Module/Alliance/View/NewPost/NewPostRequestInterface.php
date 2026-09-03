<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\NewPost;

interface NewPostRequestInterface
{
    public function getBoardId(): int;

    public function getTopicId(): int;
}
