<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\NewPost;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class NewPostRequest implements NewPostRequestInterface
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
}
