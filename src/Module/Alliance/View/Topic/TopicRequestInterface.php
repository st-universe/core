<?php

namespace Stu\Module\Alliance\View\Topic;

interface TopicRequestInterface
{
    public function getBoardId(): int;

    public function getTopicId(): int;

    public function getPageMark(): int;
}