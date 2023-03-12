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
        return $this->tidyString(
            $this->queryParameter('tname')->string()->defaultsToIfEmpty('')
        );
    }

    public function getText(): string
    {
        return $this->tidyString(
            $this->queryParameter('ttext')->string()->defaultsToIfEmpty('')
        );
    }
}
