<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\Leave;

use AccessViolation;
use AllianceJobs;
use PM;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;

final class Leave implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LEAVE_ALLIANCE';

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();
        $userId = $user->getId();

        if ($alliance->currentUserIsFounder()) {
            throw new AccessViolation();
        }

        AllianceJobs::delByUser($userId);
        $user->setAllianceId(0);
        $user->save();

        $text = sprintf(
            'Der Siedler %s hat die Allianz verlassen',
            $user->getNameWithoutMarkup()
        );

        PM::sendPM($userId, $alliance->getFounder()->getUserId(), $text);
        if ($alliance->getSuccessor()) {
            PM::sendPM($userId, $alliance->getSuccessor()->getUserId(), $text);
        }

        $game->setView('SHOW_LIST');

        $game->addInformation(_('Du hast die Allianz verlassen'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
