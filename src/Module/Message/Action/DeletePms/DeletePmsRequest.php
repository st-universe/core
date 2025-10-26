<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeletePms;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeletePmsRequest implements DeletePmsRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getDeletionIds(): array
    {
        return array_merge(
            $this->parameter('deletion_mark')->commaSeparated()->int()->defaultsTo([]),
            [$this->parameter('delete_single')->int()->defaultsTo(0)]
        );
    }
}
