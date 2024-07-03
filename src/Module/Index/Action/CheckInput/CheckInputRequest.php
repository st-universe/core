<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\CheckInput;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class CheckInputRequest implements CheckInputRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getVariable(): string
    {
        return $this->queryParameter('var')->string()->defaultsToIfEmpty('');
    }

    #[Override]
    public function getValue(): string
    {
        return $this->queryParameter('value')->string()->defaultsToIfEmpty('');
    }
}
