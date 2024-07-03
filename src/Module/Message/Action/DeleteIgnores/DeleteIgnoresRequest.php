<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteIgnores;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteIgnoresRequest implements DeleteIgnoresRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getIgnoreIds(): array
    {
        return $this->queryParameter('deletion_mark')->commaSeparated()->int()->defaultsTo([]);
    }
}
