<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeletePms;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeletePmsRequest implements DeletePmsRequestInterface
{
    use CustomControllerHelperTrait;

    public function getIgnoreIds(): array {
        return $this->queryParameter('deletion_mark')->commaSeparated()->int()->defaultsTo([]);
    }
}