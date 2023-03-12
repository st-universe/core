<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowStorage;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowStorageRequest implements ShowStorageRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }

}