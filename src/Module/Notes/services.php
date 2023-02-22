<?php

declare(strict_types=1);

namespace Stu\Module\Notes;

use Stu\Module\Control\GameController;
use Stu\Module\Notes\Action\DeleteNotes\DeleteNotes;
use Stu\Module\Notes\Action\SaveNote\SaveNote;
use Stu\Module\Notes\Action\SaveNote\SaveNoteRequest;
use Stu\Module\Notes\Action\SaveNote\SaveNoteRequestInterface;
use Stu\Module\Notes\View\Overview\Overview;
use Stu\Module\Notes\View\ShowNewNote\ShowNewNote;
use Stu\Module\Notes\View\ShowNote\ShowNote;
use Stu\Module\Notes\View\ShowNote\ShowNoteRequest;
use Stu\Module\Notes\View\ShowNote\ShowNoteRequestInterface;

use function DI\autowire;

return [
    ShowNoteRequestInterface::class => autowire(ShowNoteRequest::class),
    SaveNoteRequestInterface::class => autowire(SaveNoteRequest::class),
    'NOTES_ACTIONS' => [
        SaveNote::ACTION_IDENTIFIER => autowire(SaveNote::class),
        DeleteNotes::ACTION_IDENTIFIER => autowire(DeleteNotes::class),
    ],
    'NOTES_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        ShowNewNote::VIEW_IDENTIFIER => autowire(ShowNewNote::class),
        ShowNote::VIEW_IDENTIFIER => autowire(ShowNote::class),
    ],
];
