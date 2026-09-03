<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowContactModeSwitch;

interface ShowContactModeSwitchRequestInterface
{
    public function getContactId(): int;
}
