<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\TopicSettings;

interface TopicSettingsRequestInterface
{
    public function getTopicId(): int;
}
