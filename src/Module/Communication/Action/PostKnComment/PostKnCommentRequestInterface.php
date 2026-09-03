<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\PostKnComment;

interface PostKnCommentRequestInterface
{
    public function getKnId(): int;

    public function getText(): string;
}
