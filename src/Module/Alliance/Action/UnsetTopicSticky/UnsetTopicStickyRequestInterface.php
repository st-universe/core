<?php

namespace Stu\Module\Alliance\Action\UnsetTopicSticky;

interface UnsetTopicStickyRequestInterface
{
    public function getTopicId(): int;
}