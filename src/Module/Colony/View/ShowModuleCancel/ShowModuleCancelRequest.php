<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleCancel;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowModuleCancelRequest implements ShowModuleCancelRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }

    public function getModuleId(): int
    {
        return $this->queryParameter('module')->int()->required();
    }
}