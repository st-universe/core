<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SetTopicSticky;

interface SetTopicStickyRequestInterface
{
    public function getTopicId(): int;
}
