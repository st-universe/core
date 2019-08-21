<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\RenameTopic;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class RenameTopicRequest implements RenameTopicRequestInterface
{
    use CustomControllerHelperTrait;

    public function getTopicId(): int
    {
        return $this->queryParameter('tid')->int()->required();
    }

    public function getTitle(): string
    {
        return trim(tidyString(strip_tags(
            $this->queryParameter('tname')->string()->defaultsToIfEmpty('')
        )));
    }
}