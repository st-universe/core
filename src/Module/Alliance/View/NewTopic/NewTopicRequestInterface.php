<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\NewTopic;

interface NewTopicRequestInterface
{
    public function getBoardId(): int;
}
