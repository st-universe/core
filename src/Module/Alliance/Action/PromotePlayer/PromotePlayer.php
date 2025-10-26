<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\PromotePlayer;

use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Alliance\View\Management\Management;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PromotePlayer implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_PROMOTE_USER';

    public function __construct(
        private PromotePlayerRequestInterface $promotePlayerRequest,
        private AllianceJobRepositoryInterface $allianceJobRepository,
        private AllianceActionManagerInterface $allianceActionManager,
        private PrivateMessageSenderInterface $privateMessageSender,
        private UserRepositoryInterface $userRepository,
        private AllianceJobManagerInterface $allianceJobManager
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        if (!$this->allianceActionManager->mayEdit($alliance, $user)) {
            throw new AccessViolationException();
        }

        $jobId = $this->promotePlayerRequest->getPromotionType();
        $job = $this->allianceJobRepository->find($jobId);

        if ($job === null || $job->getAlliance()->getId() !== $alliance->getId()) {
            throw new AccessViolationException();
        }

        $promotedPlayerId = $this->promotePlayerRequest->getPlayerId();
        $promotedPlayer = $this->userRepository->find($promotedPlayerId);

        if ($promotedPlayer === null || $promotedPlayer->getAlliance()?->getId() !== $alliance->getId()) {
            throw new AccessViolationException();
        }

        $userLosesFounderRights = false;

        if ($job->hasFounderPermission()) {
            if (!$this->allianceJobManager->hasUserFounderPermission($user, $alliance)) {
                throw new AccessViolationException();
            }

            $founderJob = $alliance->getFounder();
            foreach ($founderJob->getUsers() as $oldFounder) {
                if ($oldFounder->getId() === $userId) {
                    $userLosesFounderRights = true;
                }
                $this->allianceJobManager->removeUserFromJob($oldFounder, $founderJob);
            }
        }

        $this->allianceJobManager->removeUserFromAllJobs($promotedPlayer, $alliance);
        $this->allianceJobManager->assignUserToJob($promotedPlayer, $job);

        if ($userLosesFounderRights) {
            $game->setView(ModuleEnum::ALLIANCE);
        } else {
            $game->setView(Management::VIEW_IDENTIFIER);
        }

        $text = sprintf(
            'Du wurdest zum %s der Allianz %s ernannt',
            $job->getTitle(),
            $alliance->getName()
        );

        $this->privateMessageSender->send($userId, $promotedPlayerId, $text);

        $game->getInfo()->addInformation('Das Mitglied wurde befördert');
    }



    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
