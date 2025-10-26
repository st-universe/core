<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\NewTopic;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class NewTopicRequest implements NewTopicRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getBoardId(): int
    {
        return $this->parameter('boardid')->int()->required();
    }
}
