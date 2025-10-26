<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteIgnores;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteIgnoresRequest implements DeleteIgnoresRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getIgnoreIds(): array
    {
        return $this->parameter('deletion_mark')->commaSeparated()->int()->defaultsTo([]);
    }
}
