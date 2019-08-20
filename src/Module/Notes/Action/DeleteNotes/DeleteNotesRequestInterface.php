<?php

namespace Stu\Module\Notes\Action\DeleteNotes;

interface DeleteNotesRequestInterface
{
    public function getNoteIds(): array;
}