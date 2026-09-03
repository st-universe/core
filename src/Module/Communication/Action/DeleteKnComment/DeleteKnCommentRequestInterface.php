<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteKnComment;

interface DeleteKnCommentRequestInterface
{
    public function getCommentId(): int;
}
