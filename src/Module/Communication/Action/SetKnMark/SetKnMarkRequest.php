<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\SetKnMark;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class SetKnMarkRequest implements SetKnMarkRequestInterface
{
    use CustomControllerHelperTrait;

    public function getKnOffset(): int
    {
        return $this->queryParameter('markid')->int()->required();
    }
}
