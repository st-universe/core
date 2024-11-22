<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnComments;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowKnCommentsRequest implements ShowKnCommentsRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getKnPostId(): int
    {
        return $this->parameter('knid')->int()->required();
    }
}
