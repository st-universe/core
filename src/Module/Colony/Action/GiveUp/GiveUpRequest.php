<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\GiveUp;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class GiveUpRequest implements GiveUpRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getColonyId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
