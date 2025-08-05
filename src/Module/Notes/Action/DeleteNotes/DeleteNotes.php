<?php

declare(strict_types=1);

namespace Stu\Module\Notes\Action\DeleteNotes;

use Override;
use request;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\NoteRepositoryInterface;

final class DeleteNotes implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_NOTES';

    public function __construct(private NoteRepositoryInterface $noteRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $delnotesString = request::indString('delnotes');

        if ($delnotesString === false) {
            return;
        }
        $delnotes = explode(',', $delnotesString);

        foreach ($delnotes as $noteId) {
            if ($noteId === "") {
                continue;
            }

            $obj = $this->noteRepository->find((int)$noteId);
            if ($obj === null) {
                continue;
            }
            if ($obj->getUserId() !== $game->getUser()->getId()) {
                throw new AccessViolationException();
            }
            $this->noteRepository->delete($obj);
        }

        $game->getInfo()->addInformation(_('Die ausgewählten Notizen wurden gelöscht'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
