<?php

declare(strict_types=1);

namespace Stu\Module\Notes\View\ShowNote;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowNoteRequest implements ShowNoteRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getNoteId(): int
    {
        return $this->parameter('noteid')->int()->required();
    }
}
