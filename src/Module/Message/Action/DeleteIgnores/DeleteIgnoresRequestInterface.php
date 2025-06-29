<?php

namespace Stu\Module\Message\Action\DeleteIgnores;

interface DeleteIgnoresRequestInterface
{
    /** @return array<int> */
    public function getIgnoreIds(): array;
}
