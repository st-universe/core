<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RenameBuildplan;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class RenameBuildplanRequest implements RenameBuildplanRequestInterface
{
    use CustomControllerHelperTrait;

    public function getId(): int
    {
        return $this->queryParameter('planid')->int()->required();
    }

    public function getNewName(): string
    {
        return trim(strip_tags($this->queryParameter('buildplanname')->string()->defaultsToIfEmpty('')));
    }
}
