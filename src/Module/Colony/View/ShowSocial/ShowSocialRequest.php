<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowSocial;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowSocialRequest implements ShowSocialRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
