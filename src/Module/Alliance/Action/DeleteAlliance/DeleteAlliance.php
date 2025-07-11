<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteAlliance;

use Override;
use RuntimeException;
use Stu\Component\Alliance\Enum\AllianceJobTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class DeleteAlliance implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_ALLIANCE';

    public function __construct(
        private AllianceActionManagerInterface $allianceActionManager,
        private AllianceJobRepositoryInterface $allianceJobRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();
        if ($alliance === null) {
            throw new RuntimeException('user not in alliance');
        }

        $allianceId = $alliance->getId();
        $game->setView(ModuleEnum::ALLIANCE);

        $jobFounder = $this->allianceJobRepository->getSingleResultByAllianceAndType(
            $allianceId,
            AllianceJobTypeEnum::FOUNDER
        );

        if ($jobFounder === null) {
            throw new AccessViolationException();
        }

        $jobSuccessor = $this->allianceJobRepository->getSingleResultByAllianceAndType(
            $allianceId,
            AllianceJobTypeEnum::SUCCESSOR
        );

        if (
            $jobFounder->getUser() !== $user
            && ($jobSuccessor === null || $jobSuccessor->getUser() !== $user)
        ) {
            throw new AccessViolationException();
        }

        $this->allianceActionManager->delete($jobFounder->getAlliance());

        $user->setAlliance(null);

        $this->userRepository->save($user);

        $game->addInformation(_('Die Allianz wurde gelöscht'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
