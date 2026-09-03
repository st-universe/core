<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\EditContactComment;

interface EditContactCommentRequestInterface
{
    public function getContactId(): int;

    public function getText(): string;
}
