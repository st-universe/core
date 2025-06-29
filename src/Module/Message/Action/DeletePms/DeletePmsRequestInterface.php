<?php

namespace Stu\Module\Message\Action\DeletePms;

interface DeletePmsRequestInterface
{
    /** @return array<int> */
    public function getDeletionIds(): array;
}
