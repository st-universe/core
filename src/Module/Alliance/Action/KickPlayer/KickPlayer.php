<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\KickPlayer;

use AccessViolation;
use PM;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use User;

final class KickPlayer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_KICK_USER';

    private $kickPlayerRequest;

    private $allianceJobRepository;

    private $allianceActionManager;

    public function __construct(
        KickPlayerRequestInterface $kickPlayerRequest,
        AllianceJobRepositoryInterface $allianceJobRepository,
        AllianceActionManagerInterface $allianceActionManager
    ) {
        $this->kickPlayerRequest = $kickPlayerRequest;
        $this->allianceJobRepository = $allianceJobRepository;
        $this->allianceActionManager = $allianceActionManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $alliance = $user->getAlliance();
        $allianceId = (int) $alliance->getId();

        $playerId = $this->kickPlayerRequest->getPlayerId();

        if (!$this->allianceActionManager->mayEdit($allianceId, $game->getUser()->getId())) {
            throw new AccessViolation();
        }

        $player = new User($playerId);

        if ($player->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $player->setAllianceId(0);
        $player->save();

        if ($alliance->getFounder()->getUserId() == $playerId) {
            $this->allianceJobRepository->truncateByUser($userId);

            $this->allianceActionManager->setJobForUser(
                $allianceId,
                $userId,
                ALLIANCE_JOBS_FOUNDER
            );
        }

        $this->allianceJobRepository->truncateByUser($playerId);

        $text = sprintf(
            _('Deine Mitgliedschaft in der Allianz %s wurde beendet'),
            $alliance->getName()
        );
        PM::sendPM(USER_NOONE, $playerId, $text);

        $game->addInformation(_('Der Siedler wurde rausgeworfen'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
