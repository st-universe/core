<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteContacts;

interface DeleteContactsRequestInterface
{
    /** @return array<int> */
    public function getContactIds(): array;
}
