<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnArchivePlotList;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowKnArchivePlotListRequest implements ShowKnArchivePlotListRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getVersion(): string
    {
        return $this->parameter('version')->string()->defaultsToIfEmpty('');
    }
}
