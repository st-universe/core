<?php

namespace Stu\Module\Message\Action\DeletePms;

interface DeletePmsRequestInterface
{
    public function getDeletionIds(): array;
}
