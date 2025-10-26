<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreatePost;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreatePostRequest implements CreatePostRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getBoardId(): int
    {
        return $this->parameter('boardid')->int()->required();
    }

    #[\Override]
    public function getTopicId(): int
    {
        return $this->parameter('topicid')->int()->required();
    }

    #[\Override]
    public function getText(): string
    {
        return $this->tidyString(
            $this->parameter('ttext')->string()->defaultsToIfEmpty('')
        );
    }
}
