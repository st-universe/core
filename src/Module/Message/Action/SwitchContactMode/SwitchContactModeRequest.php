<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\SwitchContactMode;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class SwitchContactModeRequest implements SwitchContactModeRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getContactId(): int
    {
        return $this->parameter('cid')->int()->required();
    }

    #[\Override]
    public function getModeId(): int
    {
        return $this->parameter('clmode')->int()->required();
    }

    #[\Override]
    public function getContactDiv(): string
    {
        return $this->tidyString($this->parameter('cldiv')->string()->defaultsToIfEmpty(''));
    }
}
