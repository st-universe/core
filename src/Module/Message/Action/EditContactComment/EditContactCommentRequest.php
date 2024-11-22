<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\EditContactComment;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditContactCommentRequest implements EditContactCommentRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getContactId(): int
    {
        return $this->parameter('edit_contact')->int()->required();
    }

    #[Override]
    public function getText(): string
    {
        return $this->tidyString(
            $this->queryParameter(sprintf('comment_%d', $this->getContactId()))->string()->trim()->defaultsToIfEmpty('')
        );
    }
}
