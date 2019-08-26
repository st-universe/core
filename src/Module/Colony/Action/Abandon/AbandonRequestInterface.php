<?php

namespace Stu\Module\Colony\Action\Abandon;

interface AbandonRequestInterface
{
    public function getColonyId(): int;
}