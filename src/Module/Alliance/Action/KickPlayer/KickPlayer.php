<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\KickPlayer;

use Stu\Exception\AccessViolation;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Component\Game\GameEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\View\Management\Management;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class KickPlayer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_KICK_USER';

    private KickPlayerRequestInterface $kickPlayerRequest;

    private AllianceJobRepositoryInterface $allianceJobRepository;

    private AllianceActionManagerInterface $allianceActionManager;

    private PrivateMessageSenderInterface $privateMessageSender;

    private UserRepositoryInterface $userRepository;

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

        if ($alliance === null) {
            throw new AccessViolation();
        }

        $allianceId = $alliance->getId();

        $playerId = $this->kickPlayerRequest->getPlayerId();

        if (!$this->allianceActionManager->mayEdit($alliance, $user)) {
            throw new AccessViolation();
        }

        $player = $this->userRepository->find($playerId);

        if ($player === null || $player->getAlliance() !== $alliance) {
            throw new AccessViolation();
        }

        $alliance->getMembers()->removeElement($player);

        $player->setAlliance(null);

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

        $game->setView(Management::VIEW_IDENTIFIER);

        $game->addInformation(_('Der Siedler wurde rausgeworfen'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
