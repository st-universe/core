<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteAvatar;

use AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Edit\Edit;

final class DeleteAvatar implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_AVATAR';

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if (!$alliance->currentUserMayEdit()) {
            new AccessViolation;
        }

        $game->setView(Edit::VIEW_IDENTIFIER);

        if ($alliance->getAvatar()) {
            @unlink(AVATAR_ALLIANCE_PATH_INTERNAL . $alliance->getAvatar() . '.png');
            $alliance->setAvatar('');
            $alliance->save();
        }
        $game->addInformation(_('Das Bild wurde gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
