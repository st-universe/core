<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowContactModeSwitch;

use Stu\Lib\Request\CustomControllerHelperTrait;
use Stu\Module\Message\View\ShowContactModeSwitch\ShowContactModeSwitchRequestInterface;

final class ShowContactModeSwitchRequest implements ShowContactModeSwitchRequestInterface
{
    use CustomControllerHelperTrait;

    public function getContactId(): int
    {
        return $this->queryParameter('cid')->int()->required();
    }
}
