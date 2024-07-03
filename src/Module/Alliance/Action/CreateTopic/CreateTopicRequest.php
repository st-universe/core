<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateTopic;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreateTopicRequest implements CreateTopicRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getBoardId(): int
    {
        return $this->queryParameter('bid')->int()->required();
    }

    #[Override]
    public function getTopicTitle(): string
    {
        return $this->tidyString(
            $this->queryParameter('tname')->string()->defaultsToIfEmpty('')
        );
    }

    #[Override]
    public function getText(): string
    {
        return $this->tidyString(
            $this->queryParameter('ttext')->string()->defaultsToIfEmpty('')
        );
    }
}
