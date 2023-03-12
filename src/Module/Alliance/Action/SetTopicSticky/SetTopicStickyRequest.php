<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SetTopicSticky;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class SetTopicStickyRequest implements SetTopicStickyRequestInterface
{
    use CustomControllerHelperTrait;

    public function getTopicId(): int
    {
        return $this->queryParameter('tid')->int()->required();
    }
}