<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\CheckInput;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CheckInputRequest implements CheckInputRequestInterface
{
    use CustomControllerHelperTrait;

    public function getVariable(): string
    {
        return $this->queryParameter('var')->string()->defaultsToIfEmpty('');
    }

    public function getValue(): string
    {
        return $this->queryParameter('value')->string()->defaultsToIfEmpty('');
    }
}
