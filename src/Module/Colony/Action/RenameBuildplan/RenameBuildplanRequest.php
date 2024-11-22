<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RenameBuildplan;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class RenameBuildplanRequest implements RenameBuildplanRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getId(): int
    {
        return $this->parameter('planid')->int()->required();
    }

    #[Override]
    public function getNewName(): string
    {
        return trim(strip_tags($this->parameter('buildplanname')->string()->defaultsToIfEmpty('')));
    }
}
