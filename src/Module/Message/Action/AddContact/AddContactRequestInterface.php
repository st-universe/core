<?php

namespace Stu\Module\Message\Action\AddContact;

interface AddContactRequestInterface
{
    public function getRecipientId(): int;

    public function getModeId(): int;

    public function getContactDiv(): string;
}
