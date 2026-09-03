<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Topic;

interface TopicRequestInterface
{
    public function getBoardId(): int;

    public function getTopicId(): int;

    public function getPageMark(): int;
}
