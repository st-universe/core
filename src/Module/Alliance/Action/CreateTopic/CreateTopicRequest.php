<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateTopic;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreateTopicRequest implements CreateTopicRequestInterface
{
    use CustomControllerHelperTrait;

    public function getBoardId(): int
    {
        return $this->queryParameter('bid')->int()->required();
    }

    public function getTopicTitle(): string
    {
        return trim(tidyString(strip_tags(
            $this->queryParameter('tname')->string()->defaultsToIfEmpty('')
        )));
    }

    public function getText(): string
    {
        return trim(tidyString(strip_tags(
            $this->queryParameter('ttext')->string()->defaultsToIfEmpty('')
        )));
    }
}