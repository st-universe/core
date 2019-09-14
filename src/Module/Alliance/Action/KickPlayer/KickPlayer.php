<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\KickPlayer;

use AccessViolation;
use PM;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use User;

final class KickPlayer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_KICK_USER';

    private $kickPlayerRequest;

    private $allianceJobRepository;

    public function __construct(
        KickPlayerRequestInterface $kickPlayerRequest,
        AllianceJobRepositoryInterface $allianceJobRepository
    ) {
        $this->kickPlayerRequest = $kickPlayerRequest;
        $this->allianceJobRepository = $allianceJobRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $alliance = $user->getAlliance();

        $playerId = $this->kickPlayerRequest->getPlayerId();

        if (!$alliance->currentUserMayEdit() || $playerId === $userId) {
            throw new AccessViolation();
        }

        $player = new User($playerId);

        if ($player->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $player->setAllianceId(0);
        $player->save();

        if ($alliance->getFounder()->getUserId() == $playerId) {
            $alliance->setFounder($userId);
            $alliance->delSuccessor();
        }

        $this->allianceJobRepository->truncateByUser($playerId);

        $text = sprintf(
            _('Deine Mitgliedschaft in der Allianz %s wurde beendet'),
            $alliance->getNameWithoutMarkup()
        );
        PM::sendPM(USER_NOONE, $playerId, $text);

        $game->addInformation(_('Der Siedler wurde rausgeworfen'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
