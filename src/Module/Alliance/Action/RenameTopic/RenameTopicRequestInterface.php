<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\RenameTopic;

interface RenameTopicRequestInterface
{
    public function getTopicId(): int;

    public function getTitle(): string;
}
