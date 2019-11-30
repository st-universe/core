<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\EditContactComment;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditContactCommentRequest implements EditContactCommentRequestInterface
{
    use CustomControllerHelperTrait;

    public function getContactId(): int
    {
        return $this->queryParameter('edit_contact')->int()->required();
    }

    public function getText(): string
    {
        return $this->tidyString(
            $this->queryParameter(sprintf('comment_%d', $this->getContactId()))->string()->trim()->defaultsToIfEmpty('')
        );
    }
}
