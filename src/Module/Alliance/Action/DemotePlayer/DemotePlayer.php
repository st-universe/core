<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DemotePlayer;

use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
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
    public const ACTION_IDENTIFIER = 'B_DEMOTE_USER';

    private DemotePlayerRequestInterface $promotePlayerRequest;

    private AllianceJobRepositoryInterface $allianceJobRepository;

    private AllianceActionManagerInterface $allianceActionManager;

    private PrivateMessageSenderInterface $privateMessageSender;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        DemotePlayerRequestInterface $demotePlayerRequest,
        AllianceJobRepositoryInterface $allianceJobRepository,
        AllianceActionManagerInterface $allianceActionManager,
        PrivateMessageSenderInterface $privateMessageSender,
        UserRepositoryInterface $userRepository
    ) {
        $this->promotePlayerRequest = $demotePlayerRequest;
        $this->allianceJobRepository = $allianceJobRepository;
        $this->allianceActionManager = $allianceActionManager;
        $this->privateMessageSender = $privateMessageSender;
        $this->userRepository = $userRepository;
    }

    /**
     * Demotes a player
     */
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $alliance = $user->getAlliance();
        $allianceId = $alliance->getId();
        $playerId = $this->promotePlayerRequest->getPlayerId();

        if (!$this->allianceActionManager->mayEdit($allianceId, $userId)) {
            throw new AccessViolation();
        }

        $player = $this->userRepository->find($playerId);

        if (
            $player === null
            || $player->getId() === $userId
            || $player->getAllianceId() !== $allianceId
        ) {
            throw new AccessViolation();
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

        $game->addInformation('Das Mitglied wurde von seinem Posten enthoben');
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
