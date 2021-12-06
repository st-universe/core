<?php

namespace Stu\Module\Message\Action\AddContact;

interface AddContactRequestInterface
{
    public function getRecipientId(): string;

    public function getModeId(): int;

    public function getContactDiv(): string;
}
