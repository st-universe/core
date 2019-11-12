<?php

namespace Stu\Module\Message\Action\DeleteIgnores;

interface DeleteIgnoresRequestInterface
{
    public function getIgnoreIds(): array;
}
