<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ChangeName;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ChangeNameRequest implements ChangeNameRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getName(): string
    {
        return $this->tidyString(
            $this->queryParameter('shipname')->string()->defaultsToIfEmpty('')
        );
    }
}
