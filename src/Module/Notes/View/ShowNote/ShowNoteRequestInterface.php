<?php

declare(strict_types=1);

namespace Stu\Module\Notes\View\ShowNote;

interface ShowNoteRequestInterface
{
    public function getNoteId(): int;
}
