<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnArchive;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowKnArchiveRequest implements ShowKnArchiveRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getVersion(): string
    {
        return $this->parameter('version')->string()->defaultsToIfEmpty('v1.0');
    }

    #[\Override]
    public function getPage(): int
    {
        return $this->parameter('page')->int()->defaultsTo(1);
    }

    #[\Override]
    public function getMark(): int
    {
        return $this->parameter('mark')->int()->defaultsTo(0);
    }
}
