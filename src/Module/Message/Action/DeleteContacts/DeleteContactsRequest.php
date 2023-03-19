<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteContacts;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteContactsRequest implements DeleteContactsRequestInterface
{
    use CustomControllerHelperTrait;

    public function getContactIds(): array
    {
        return $this->queryParameter('deletion_mark')->commaSeparated()->int()->defaultsTo([]);
    }
}
