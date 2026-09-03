<?php

declare(strict_types=1);

namespace Stu\Module\Notes\Action\SaveNote;

interface SaveNoteRequestInterface
{
    public function getNoteId(): int;

    public function getTitle(): string;

    public function getText(): string;
}
