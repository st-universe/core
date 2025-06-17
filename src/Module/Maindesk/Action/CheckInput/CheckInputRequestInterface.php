<?php

namespace Stu\Module\Maindesk\Action\CheckInput;

interface CheckInputRequestInterface
{
    public function getVariable(): string;

    public function getValue(): string;
}
