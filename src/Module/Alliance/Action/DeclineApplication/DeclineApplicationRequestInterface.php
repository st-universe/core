<?php

namespace Stu\Module\Alliance\Action\DeclineApplication;

interface DeclineApplicationRequestInterface
{
    public function getApplicationId(): int;
}