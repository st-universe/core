<?php

namespace Stu\Module\Message\Action\EditContactComment;

interface EditContactCommentRequestInterface
{
    public function getContactId(): int;

    public function getText(): string;
}
