<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowEditKn;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowEditKnRequest implements ShowEditKnRequestInterface
{
    use CustomControllerHelperTrait;

    public function getPostId(): int
    {
        return $this->queryParameter('knid')->int()->required();
    }
}