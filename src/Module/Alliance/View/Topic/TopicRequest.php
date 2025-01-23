<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Topic;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class TopicRequest implements TopicRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getBoardId(): int
    {
        return $this->parameter('boardid')->int()->required();
    }

    #[Override]
    public function getTopicId(): int
    {
        return $this->parameter('topicid')->int()->required();
    }

    #[Override]
    public function getPageMark(): int
    {
        return $this->parameter('mark')->int()->defaultsTo(0);
    }
}
