<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeTorpedoType;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ChangeTorpedoTypeRequest implements ChangeTorpedoTypeRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getTorpedoId(): int
    {
        return $this->parameter('torpid')->int()->required();
    }
}
