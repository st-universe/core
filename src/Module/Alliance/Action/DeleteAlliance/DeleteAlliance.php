<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteAlliance;

use AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\AllianceList\AllianceList;

final class DeleteAlliance implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_ALLIANCE';

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if (!$alliance->currentUserMayEdit()) {
            new AccessViolation;
        }

        $game->setView(AllianceList::VIEW_IDENTIFIER);

        if (!$alliance->currentUserIsFounder()) {
            throw new AccessViolation();
        }

        $alliance->delete();

        $user->setAllianceId(0);
        $user->save();

        $game->addInformation(_('Die Allianz wurde gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
