<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteAlliance;

use Stu\Component\Alliance\AllianceEnum;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class DeleteAlliance implements ActionControllerInterface
{
    /**
     * @var string
     */
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
        $allianceId = $alliance->getId();

        $game->setView(ModuleViewEnum::ALLIANCE);

        $jobFounder = $this->allianceJobRepository->getSingleResultByAllianceAndType(
            $allianceId,
            AllianceEnum::ALLIANCE_JOBS_FOUNDER
        );

        $jobSuccessor = $this->allianceJobRepository->getSingleResultByAllianceAndType(
            $allianceId,
            AllianceEnum::ALLIANCE_JOBS_SUCCESSOR
        );

        if (
            $jobFounder->getUserId() !== $user->getId()
            && ($jobSuccessor === null || $jobSuccessor->getUserId() !== $user->getId())
        ) {
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
