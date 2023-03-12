<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeletePms;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeletePmsRequest implements DeletePmsRequestInterface
{
    use CustomControllerHelperTrait;

    public function getDeletionIds(): array {
        return array_merge(
            $this->queryParameter('deletion_mark')->commaSeparated()->int()->defaultsTo([]),
            [$this->queryParameter('delete_single')->int()->defaultsTo(0)]
        );
    }
}
