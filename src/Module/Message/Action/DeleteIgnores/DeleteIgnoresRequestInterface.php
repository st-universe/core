<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteIgnores;

interface DeleteIgnoresRequestInterface
{
    /** @return array<int> */
    public function getIgnoreIds(): array;
}
