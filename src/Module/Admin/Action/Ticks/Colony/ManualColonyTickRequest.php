<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks\Colony;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ManualColonyTickRequest implements ManualColonyTickRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): ?int
    {
        return $this->queryParameter('colonyid')->int()->defaultsTo(null);
    }
}
