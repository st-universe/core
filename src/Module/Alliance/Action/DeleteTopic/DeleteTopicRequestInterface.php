<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteTopic;

interface DeleteTopicRequestInterface
{
    public function getTopicId(): int;
}
