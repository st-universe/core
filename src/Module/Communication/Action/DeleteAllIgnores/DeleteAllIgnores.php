<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteAllIgnores;

use Ignorelist;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;

final class DeleteAllIgnores implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_ALL_IGNORES';

    public function handle(GameControllerInterface $game): void
    {
        Ignorelist::truncate(sprintf('WHERE user_id = %d', $game->getUser()->getId()));

        $game->addInformation(_('Die Einträge wurden gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
