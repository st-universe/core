<?php

namespace Stu\Module\Message\Action\DeleteContacts;

interface DeleteContactsRequestInterface
{
    public function getContactIds(): array;
}
