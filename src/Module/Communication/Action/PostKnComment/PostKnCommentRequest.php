<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\PostKnComment;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class PostKnCommentRequest implements PostKnCommentRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getKnId(): int
    {
        return $this->parameter('knid')->int()->required();
    }

    #[\Override]
    public function getText(): string
    {
        return $this->tidyString(
            $this->parameter('comment')->string()->trim()->defaultsToIfEmpty('')
        );
    }
}
