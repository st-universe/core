<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\SwitchContactMode;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class SwitchContactModeRequest implements SwitchContactModeRequestInterface
{
    use CustomControllerHelperTrait;

    public function getContactId(): int
    {
        return $this->queryParameter('cid')->int()->required();
    }

    public function getModeId(): int
    {
        return $this->queryParameter('clmode')->int()->required();
    }

    public function getContactDiv(): string
    {
        return $this->tidyString($this->queryParameter('cldiv')->string()->defaultsToIfEmpty(''));
    }
}
