<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnArchiveComments;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowKnArchiveCommentsRequest implements ShowKnArchiveCommentsRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getKnPostId(): int
    {
        return $this->parameter('knid')->int()->required();
    }
}
