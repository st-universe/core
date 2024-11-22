<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\PostKnComment;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class PostKnCommentRequest implements PostKnCommentRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getPostId(): int
    {
        return $this->parameter('posting')->int()->required();
    }

    #[Override]
    public function getText(): string
    {
        return $this->tidyString(
            $this->parameter('comment')->string()->trim()->defaultsToIfEmpty('')
        );
    }
}
