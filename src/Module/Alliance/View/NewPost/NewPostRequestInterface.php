<?php

namespace Stu\Module\Alliance\View\NewPost;

interface NewPostRequestInterface
{
    public function getBoardId(): int;

    public function getTopicId(): int;
}
