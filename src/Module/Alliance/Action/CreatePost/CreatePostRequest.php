<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreatePost;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreatePostRequest implements CreatePostRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getBoardId(): int
    {
        return $this->queryParameter('bid')->int()->required();
    }

    #[Override]
    public function getTopicId(): int
    {
        return $this->queryParameter('tid')->int()->required();
    }

    #[Override]
    public function getText(): string
    {
        return $this->tidyString(
            $this->queryParameter('ttext')->string()->defaultsToIfEmpty('')
        );
    }
}
