<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteAlliance;

use AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\AllianceList\AllianceList;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

final class DeleteAlliance implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_ALLIANCE';

    private $allianceActionManager;

    private $allianceJobRepository;

    public function __construct(
        AllianceActionManagerInterface $allianceActionManager,
        AllianceJobRepositoryInterface $allianceJobRepository
    ) {
        $this->allianceActionManager = $allianceActionManager;
        $this->allianceJobRepository = $allianceJobRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();
        $allianceId = (int) $alliance->getId();

        $game->setView(AllianceList::VIEW_IDENTIFIER);

        $job = $this->allianceJobRepository->getSingleResultByAllianceAndType($allianceId, ALLIANCE_JOBS_FOUNDER);

        if ($job->getUserId() !== $user->getId()) {
            throw new AccessViolation();
        }

        $this->allianceActionManager->delete($allianceId);

        $user->setAllianceId(0);
        $user->save();

        $game->addInformation(_('Die Allianz wurde gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
