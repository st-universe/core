<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\KickPlayer;

use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Alliance\View\Management\Management;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Repository\UserRepositoryInterface;

final class KickPlayer implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_KICK_USER';

    public function __construct(
        private KickPlayerRequestInterface $kickPlayerRequest,
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

        $playerId = $this->kickPlayerRequest->getPlayerId();

        if (!$this->allianceActionManager->mayEdit($alliance, $user)) {
            throw new AccessViolationException();
        }

        $player = $this->userRepository->find($playerId);

        if ($player === null || $player->getAlliance()?->getId() !== $alliance->getId()) {
            throw new AccessViolationException();
        }

        $alliance->getMembers()->removeElement($player);

        $isKickedPlayerFounder = $this->allianceJobManager->hasUserFounderPermission($player, $alliance);

        if ($isKickedPlayerFounder) {
            $founderJob = $alliance->getFounder();
            $this->allianceJobManager->removeUserFromJob($player, $founderJob);
            $this->allianceJobManager->assignUserToJob($user, $founderJob);
        }

        $this->allianceJobManager->removeUserFromAllJobs($player, $alliance);

        $player->setAlliance(null);
        $this->userRepository->save($player);

        $text = sprintf(
            _('Deine Mitgliedschaft in der Allianz %s wurde beendet'),
            $alliance->getName()
        );

        $this->privateMessageSender->send(UserConstants::USER_NOONE, $playerId, $text);

        $game->setView(Management::VIEW_IDENTIFIER);

        $game->getInfo()->addInformation(_('Der Siedler wurde rausgeworfen'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
