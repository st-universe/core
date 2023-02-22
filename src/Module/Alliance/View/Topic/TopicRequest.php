<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Topic;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class TopicRequest implements TopicRequestInterface
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

    public function getPageMark(): int
    {
        return $this->queryParameter('mark')->int()->defaultsTo(0);
    }
}
