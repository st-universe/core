<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\UnsetTopicSticky;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class UnsetTopicStickyRequest implements UnsetTopicStickyRequestInterface
{
    use CustomControllerHelperTrait;

    public function getTopicId(): int
    {
        return $this->queryParameter('tid')->int()->required();
    }
}
