<?php

namespace Stu\Module\Message\Action\DeleteContacts;

interface DeleteContactsRequestInterface
{
    /** @return array<int> */
    public function getContactIds(): array;
}
