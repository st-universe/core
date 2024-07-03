<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\AddContact;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class AddContactRequest implements AddContactRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getRecipientId(): string
    {
        return $this->queryParameter('recid')->string()->defaultsToIfEmpty('');
    }

    #[Override]
    public function getModeId(): int
    {
        return $this->queryParameter('clmode')->int()->required();
    }

    #[Override]
    public function getContactDiv(): string
    {
        return $this->tidyString($this->queryParameter('cldiv')->string()->defaultsToIfEmpty(''));
    }
}
