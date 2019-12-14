<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteAlliance;

use Stu\Exception\AccessViolation;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\AllianceList\AllianceList;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class DeleteAlliance implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_ALLIANCE';

    private AllianceActionManagerInterface $allianceActionManager;

    private AllianceJobRepositoryInterface $allianceJobRepository;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        AllianceActionManagerInterface $allianceActionManager,
        AllianceJobRepositoryInterface $allianceJobRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->allianceActionManager = $allianceActionManager;
        $this->allianceJobRepository = $allianceJobRepository;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();
        $allianceId = (int) $alliance->getId();

        $game->setView(AllianceList::VIEW_IDENTIFIER);

        $job = $this->allianceJobRepository->getSingleResultByAllianceAndType($allianceId,
            AllianceEnum::ALLIANCE_JOBS_FOUNDER);

        if ($job->getUserId() !== $user->getId()) {
            throw new AccessViolation();
        }

        $this->allianceActionManager->delete($allianceId);

        $user->setAlliance(null);

        $this->userRepository->save($user);

        $game->addInformation(_('Die Allianz wurde gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
