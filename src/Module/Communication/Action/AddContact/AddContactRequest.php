<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddContact;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class AddContactRequest implements AddContactRequestInterface
{
    use CustomControllerHelperTrait;

    public function getRecipientId(): int
    {
        return $this->queryParameter('recid')->int()->required();
    }

    public function getModeId(): int
    {
        return $this->queryParameter('clmode')->int()->required();
    }

    public function getContactDiv(): string
    {
        return $this->tidyString($this->queryParameter('cldiv')->string()->defaultsToIfEmpty(''));
    }
}
