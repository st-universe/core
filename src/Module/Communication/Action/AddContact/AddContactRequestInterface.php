<?php

namespace Stu\Module\Communication\Action\AddContact;

interface AddContactRequestInterface
{
    public function getRecipientId(): int;

    public function getModeId(): int;

    public function getContactDiv(): string;
}