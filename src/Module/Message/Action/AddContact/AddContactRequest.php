<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\AddContact;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class AddContactRequest implements AddContactRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getRecipientId(): string
    {
        return $this->parameter('recid')->string()->defaultsToIfEmpty('');
    }

    #[\Override]
    public function getModeId(): int
    {
        return $this->parameter('clmode')->int()->required();
    }

    #[\Override]
    public function getContactDiv(): string
    {
        return $this->tidyString($this->parameter('cldiv')->string()->defaultsToIfEmpty(''));
    }
}
