<?php

namespace Stu\Module\Alliance\Action\CreatePost;

interface CreatePostRequestInterface
{
    public function getBoardId(): int;

    public function getTopicId(): int;

    public function getText(): string;
}