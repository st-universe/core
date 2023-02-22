<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\NewTopic;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class NewTopicRequest implements NewTopicRequestInterface
{
    use CustomControllerHelperTrait;

    public function getBoardId(): int
    {
        return $this->queryParameter('bid')->int()->required();
    }
}
