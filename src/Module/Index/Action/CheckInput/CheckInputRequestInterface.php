<?php

namespace Stu\Module\Index\Action\CheckInput;

interface CheckInputRequestInterface
{
    public function getVariable(): string;

    public function getValue(): string;
}