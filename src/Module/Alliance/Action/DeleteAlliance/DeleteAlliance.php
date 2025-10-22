<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteAlliance;

use Override;
use RuntimeException;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class DeleteAlliance implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_ALLIANCE';

    public function __construct(
        private AllianceActionManagerInterface $allianceActionManager,
        private UserRepositoryInterface $userRepository,
        private AllianceJobManagerInterface $allianceJobManager
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();
        if ($alliance === null) {
            throw new RuntimeException('user not in alliance');
        }

        $game->setView(ModuleEnum::ALLIANCE);

        $isFounder = $this->allianceJobManager->hasUserFounderPermission($user, $alliance);
        $isSuccessor = $this->allianceJobManager->hasUserSuccessorPermission($user, $alliance);

        if (!$isFounder && !$isSuccessor) {
            throw new AccessViolationException();
        }

        $this->allianceActionManager->delete($alliance);

        $user->setAlliance(null);

        $this->userRepository->save($user);

        $game->getInfo()->addInformation(_('Die Allianz wurde gelöscht'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
