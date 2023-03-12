<?php

namespace Stu\Module\Alliance\Action\CreateTopic;

interface CreateTopicRequestInterface
{
    public function getBoardId(): int;

    public function getTopicTitle(): string;

    public function getText(): string;
}