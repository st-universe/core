<?php

declare(strict_types=1);

namespace Stu\Module\Notes\Action\DeleteNotes;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteNotesRequest implements DeleteNotesRequestInterface
{
    use CustomControllerHelperTrait;

    public function getNoteIds(): array{
        return $this->queryParameter('delnotes')->commaSeparated()->int()->required();
    }
}