<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks\Colony;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ManualColonyTickRequest implements ManualColonyTickRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getColonyId(): ?int
    {
        return $this->queryParameter('colonyid')->int()->defaultsTo(null);
    }

    #[Override]
    public function getGroupId(): ?int
    {
        return $this->queryParameter('groupid')->int()->defaultsTo(null);
    }
}
