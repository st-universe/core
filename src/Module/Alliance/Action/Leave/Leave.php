<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\Leave;

use AccessViolation;
use PM;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

final class Leave implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LEAVE_ALLIANCE';

    private $allianceJobRepository;

    public function __construct(
        AllianceJobRepositoryInterface $allianceJobRepository
    ) {
        $this->allianceJobRepository = $allianceJobRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();
        $userId = $user->getId();

        $foundJob = $this->allianceJobRepository->getSingleResultByAllianceAndType(
            (int) $alliance->getId(),
            ALLIANCE_JOBS_FOUNDER
        );

        if ($foundJob->getUserId() === $userId) {
            throw new AccessViolation();
        }

        $this->allianceJobRepository->truncateByUser($userId);

        $user->setAllianceId(0);
        $user->save();

        $text = sprintf(
            'Der Siedler %s hat die Allianz verlassen',
            $user->getName()
        );

        PM::sendPM($userId, $foundJob->getUserId(), $text);
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
