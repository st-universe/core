<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreatePost;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreatePostRequest implements CreatePostRequestInterface
{
    use CustomControllerHelperTrait;

    public function getBoardId(): int
    {
        return $this->queryParameter('bid')->int()->required();
    }

    public function getTopicId(): int
    {
        return $this->queryParameter('tid')->int()->required();
    }

    public function getText(): string
    {
        return $this->tidyString(
            $this->queryParameter('ttext')->string()->defaultsToIfEmpty('')
        );
    }
}
