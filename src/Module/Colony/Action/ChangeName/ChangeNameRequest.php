<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeName;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ChangeNameRequest implements ChangeNameRequestInterface
{
    use CustomControllerHelperTrait;

    public function getName(): string
    {
        return $this->tidyString(
            $this->queryParameter('colname')->string()->defaultsToIfEmpty('')
        );
    }
}
