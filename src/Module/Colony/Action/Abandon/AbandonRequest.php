<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\Abandon;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class AbandonRequest implements AbandonRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int {
        return $this->queryParameter('id')->int()->required();
    }

}