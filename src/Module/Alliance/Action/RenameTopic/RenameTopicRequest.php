<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\RenameTopic;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class RenameTopicRequest implements RenameTopicRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getTopicId(): int
    {
        return $this->queryParameter('tid')->int()->required();
    }

    #[Override]
    public function getTitle(): string
    {
        return $this->tidyString(
            $this->queryParameter('tname')->string()->defaultsToIfEmpty('')
        );
    }
}
