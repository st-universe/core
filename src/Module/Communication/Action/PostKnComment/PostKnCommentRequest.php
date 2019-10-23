<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\PostKnComment;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class PostKnCommentRequest implements PostKnCommentRequestInterface
{
    use CustomControllerHelperTrait;

    public function getPostId(): int
    {
        return $this->queryParameter('posting')->int()->required();
    }

    public function getText(): string
    {
        return $this->tidyString(
            $this->queryParameter('comment')->string()->trim()->defaultsToIfEmpty('')
        );
    }
}
