<?php

namespace Stu\Module\Colony\Action\GiveUp;

interface GiveUpRequestInterface
{
    public function getColonyId(): int;
}