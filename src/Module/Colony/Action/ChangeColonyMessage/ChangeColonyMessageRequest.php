<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeColonyMessage;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ChangeColonyMessageRequest implements ChangeColonyMessageRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getColonyMessage(): string
    {
        return $this->tidyString(
            $this->parameter('colony_message')->string()->defaultsToIfEmpty('')
        );
    }
}
