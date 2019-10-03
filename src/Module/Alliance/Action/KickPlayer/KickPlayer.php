<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\KickPlayer;

use AccessViolation;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Component\Game\GameEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Communication\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class KickPlayer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_KICK_USER';

    private $kickPlayerRequest;

    private $allianceJobRepository;

    private $allianceActionManager;

    private $privateMessageSender;

    private $userRepository;

    public function __construct(
        KickPlayerRequestInterface $kickPlayerRequest,
        AllianceJobRepositoryInterface $allianceJobRepository,
        AllianceActionManagerInterface $allianceActionManager,
        PrivateMessageSenderInterface $privateMessageSender,
        UserRepositoryInterface $userRepository
    ) {
        $this->kickPlayerRequest = $kickPlayerRequest;
        $this->allianceJobRepository = $allianceJobRepository;
        $this->allianceActionManager = $allianceActionManager;
        $this->privateMessageSender = $privateMessageSender;
        $this->userRepository = $userRepository;
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

        $player = $this->userRepository->find($playerId);

        if ($player === null || $player->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $player->setAlliance(null);
        
        $alliance->getMembers()->removeElement($player);

        $this->userRepository->save($player);

        if ($alliance->getFounder()->getUserId() == $playerId) {
            $this->allianceJobRepository->truncateByUser($userId);

            $this->allianceActionManager->setJobForUser(
                $allianceId,
                $userId,
                AllianceEnum::ALLIANCE_JOBS_FOUNDER
            );
        }

        $this->allianceJobRepository->truncateByUser($playerId);

        $text = sprintf(
            _('Deine Mitgliedschaft in der Allianz %s wurde beendet'),
            $alliance->getName()
        );

        $this->privateMessageSender->send(GameEnum::USER_NOONE, $playerId, $text);

        $game->addInformation(_('Der Siedler wurde rausgeworfen'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
