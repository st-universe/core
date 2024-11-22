<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SetTopicSticky;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class SetTopicStickyRequest implements SetTopicStickyRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getTopicId(): int
    {
        return $this->parameter('topicid')->int()->required();
    }
}
