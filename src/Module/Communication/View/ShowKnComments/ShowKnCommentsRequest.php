<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnComments;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowKnCommentsRequest implements ShowKnCommentsRequestInterface
{
    use CustomControllerHelperTrait;

    public function getKnPostId(): int
    {
        return $this->queryParameter('posting')->int()->required();
    }
}
