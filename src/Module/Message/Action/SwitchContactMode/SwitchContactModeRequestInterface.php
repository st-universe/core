<?php

namespace Stu\Module\Message\Action\SwitchContactMode;

interface SwitchContactModeRequestInterface
{
    public function getContactId(): int;

    public function getModeId(): int;

    public function getContactDiv(): string;
}
