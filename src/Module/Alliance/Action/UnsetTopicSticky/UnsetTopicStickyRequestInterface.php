<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\UnsetTopicSticky;

interface UnsetTopicStickyRequestInterface
{
    public function getTopicId(): int;
}
