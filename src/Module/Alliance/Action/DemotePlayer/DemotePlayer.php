<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DemotePlayer;

use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\View\Management\Management;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Demotes the player (if he has a job)
 */
final class DemotePlayer implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEMOTE_USER';

    public function __construct(
        private DemotePlayerRequestInterface $demotePlayerRequest,
        private AllianceJobRepositoryInterface $allianceJobRepository,
        private AllianceActionManagerInterface $allianceActionManager,
        private PrivateMessageSenderInterface $privateMessageSender,
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Demotes a player
     */
    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $alliance = $user->getAlliance();
        $playerId = $this->demotePlayerRequest->getPlayerId();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        if (!$this->allianceActionManager->mayEdit($alliance, $user)) {
            throw new AccessViolationException();
        }

        $player = $this->userRepository->find($playerId);

        if (
            $player === null
            || $player->getId() === $userId
            || $player->getAlliance()?->getId() !== $alliance->getId()
        ) {
            throw new AccessViolationException();
        }

        $this->allianceJobRepository->truncateByUser($playerId);

        $this->privateMessageSender->send(
            $userId,
            $playerId,
            sprintf(
                'Du wurdest von Deinem Posten in der Allianz %s entbunden',
                $alliance->getName()
            )
        );

        $game->setView(Management::VIEW_IDENTIFIER);

        $game->getInfo()->addInformation('Das Mitglied wurde von seinem Posten enthoben');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
