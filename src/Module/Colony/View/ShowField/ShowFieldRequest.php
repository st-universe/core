<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowField;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowFieldRequest implements ShowFieldRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }

    public function getFieldId(): int
    {
        return $this->queryParameter('fid')->int()->required();
    }

}