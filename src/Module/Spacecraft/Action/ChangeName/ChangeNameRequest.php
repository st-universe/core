<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\ChangeName;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ChangeNameRequest implements ChangeNameRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getName(): string
    {
        return $this->tidyString(
            $this->parameter('shipname')->string()->defaultsToIfEmpty('')
        );
    }
}
