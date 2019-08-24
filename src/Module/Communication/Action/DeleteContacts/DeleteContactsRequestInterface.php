<?php

namespace Stu\Module\Communication\Action\DeleteContacts;

interface DeleteContactsRequestInterface
{
    public function getContactIds(): array;
}