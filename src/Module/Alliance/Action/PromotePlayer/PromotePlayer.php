<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\PromotePlayer;

use Override;
use Stu\Component\Alliance\Enum\AllianceJobTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
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
        private UserRepositoryInterface $userRepository
    ) {}

    #[Override]
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

        $promotedPlayerId = $this->promotePlayerRequest->getPlayerId();
        $promotedPlayer = $this->userRepository->find($promotedPlayerId);

        if ($promotedPlayer === null || $promotedPlayer->getAlliance() !== $alliance) {
            throw new AccessViolationException();
        }

        $type = AllianceJobTypeEnum::from($this->promotePlayerRequest->getPromotionType());
        $availablePromotions = [
            AllianceJobTypeEnum::FOUNDER,
            AllianceJobTypeEnum::SUCCESSOR,
            AllianceJobTypeEnum::DIPLOMATIC,
        ];

        if (!in_array($type, $availablePromotions)) {
            throw new AccessViolationException();
        }

        $founderJob = $alliance->getFounder();

        if ($founderJob->getUserId() === $promotedPlayerId) {
            throw new AccessViolationException();
        }

        $this->allianceJobRepository->truncateByUser($promotedPlayerId);
        $alliance->getJobs()->remove($type->value);

        $game->setView(Management::VIEW_IDENTIFIER);

        $text = match ($type) {
            AllianceJobTypeEnum::FOUNDER => $this->setFounder($founderJob, $promotedPlayer, $game),
            AllianceJobTypeEnum::SUCCESSOR => $this->setSuccessor($user, $alliance, $promotedPlayer),
            AllianceJobTypeEnum::DIPLOMATIC => $this->setDiplomatic($user, $alliance, $promotedPlayer),
        };

        $this->privateMessageSender->send($userId, $promotedPlayerId, $text);

        $game->addInformation(_('Das Mitglied wurde befördert'));
    }

    private function setFounder(AllianceJob $founderJob, User $promotedPlayer, GameControllerInterface $game): string
    {
        if ($founderJob->getUser() !== $game->getUser()) {
            throw new AccessViolationException();
        }
        $this->allianceActionManager->setJobForUser(
            $founderJob->getAlliance(),
            $promotedPlayer,
            AllianceJobTypeEnum::FOUNDER
        );

        $game->setView(ModuleEnum::ALLIANCE);

        return sprintf(
            _('Du wurdest zum neuen Präsidenten der Allianz %s ernannt'),
            $founderJob->getAlliance()->getName()
        );
    }

    private function setSuccessor(User $user, Alliance $alliance, User $promotedPlayer): string
    {
        if ($user === $promotedPlayer) {
            throw new AccessViolationException();
        }

        $this->allianceActionManager->setJobForUser(
            $alliance,
            $promotedPlayer,
            AllianceJobTypeEnum::SUCCESSOR
        );

        return sprintf(
            _('Du wurdest zum neuen Vize-Präsidenten der Allianz %s ernannt'),
            $alliance->getName()
        );
    }

    private function setDiplomatic(User $user, Alliance $alliance, User $promotedPlayer): string
    {
        if ($user === $promotedPlayer) {
            throw new AccessViolationException();
        }

        $this->allianceActionManager->setJobForUser(
            $alliance,
            $promotedPlayer,
            AllianceJobTypeEnum::DIPLOMATIC
        );

        return sprintf(
            'Du wurdest zum neuen Außenminister der Allianz %s ernannt',
            $alliance->getName()
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
