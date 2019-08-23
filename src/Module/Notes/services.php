<?php

declare(strict_types=1);

namespace Stu\Module\Notes;

use Stu\Control\GameController;
use Stu\Control\IntermediateController;
use Stu\Lib\SessionInterface;
use Stu\Module\Notes\Action\DeleteNotes\DeleteNotes;
use Stu\Module\Notes\Action\DeleteNotes\DeleteNotesRequest;
use Stu\Module\Notes\Action\DeleteNotes\DeleteNotesRequestInterface;
use Stu\Module\Notes\Action\SaveNote\SaveNote;
use Stu\Module\Notes\Action\SaveNote\SaveNoteRequest;
use Stu\Module\Notes\Action\SaveNote\SaveNoteRequestInterface;
use Stu\Module\Notes\View\Overview\Overview;
use Stu\Module\Notes\View\ShowNewNote\ShowNewNote;
use Stu\Module\Notes\View\ShowNote\ShowNote;
use Stu\Module\Notes\View\ShowNote\ShowNoteRequest;
use Stu\Module\Notes\View\ShowNote\ShowNoteRequestInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    ShowNoteRequestInterface::class => autowire(ShowNoteRequest::class),
    SaveNoteRequestInterface::class => autowire(SaveNoteRequest::class),
    DeleteNotesRequestInterface::class => autowire(DeleteNotesRequest::class),
    IntermediateController::TYPE_NOTES => create(IntermediateController::class)
        ->constructor(
            get(SessionInterface::class),
            get(SessionStringRepositoryInterface::class),
            [
                SaveNote::ACTION_IDENTIFIER => autowire(SaveNote::class),
                DeleteNotes::ACTION_IDENTIFIER => autowire(DeleteNotes::class),
            ],
            [
                GameController::DEFAULT_VIEW => autowire(Overview::class),
                ShowNewNote::VIEW_IDENTIFIER => autowire(ShowNewNote::class),
                ShowNote::VIEW_IDENTIFIER => autowire(ShowNote::class),
            ]
        ),
];