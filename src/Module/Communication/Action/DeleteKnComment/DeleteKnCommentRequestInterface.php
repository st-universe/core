<?php

namespace Stu\Module\Communication\Action\DeleteKnComment;

interface DeleteKnCommentRequestInterface
{
    public function getCommentId(): int;
}