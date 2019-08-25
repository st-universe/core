<?php

namespace Stu\Module\Communication\Action\EditContactComment;

interface EditContactCommentRequestInterface
{
    public function getContactId(): int;

    public function getText(): string;
}