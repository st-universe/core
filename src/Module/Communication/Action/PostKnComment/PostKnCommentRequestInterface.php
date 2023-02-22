<?php

namespace Stu\Module\Communication\Action\PostKnComment;

interface PostKnCommentRequestInterface
{
    public function getPostId(): int;

    public function getText(): string;
}
