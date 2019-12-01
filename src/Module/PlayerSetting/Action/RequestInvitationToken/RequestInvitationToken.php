<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\RequestInvitationToken;

use Noodlehaus\ConfigInterface;
use Stu\Component\Player\Invitation\InvitePlayerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserInvitationRepositoryInterface;

final class RequestInvitationToken implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_REQUEST_INVITATION_TOKEN';

    private UserInvitationRepositoryInterface $invitationRepository;

    private ConfigInterface $config;

    private InvitePlayerInterface $invitePlayer;

    public function __construct(
        UserInvitationRepositoryInterface $invitationRepository,
        ConfigInterface $config,
        InvitePlayerInterface $invitePlayer
    ) {
        $this->invitationRepository = $invitationRepository;
        $this->config = $config;
        $this->invitePlayer = $invitePlayer;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $currentInvitations = $this->invitationRepository->getInvitationsByUser($user);

        if (count($currentInvitations) >= $this->config->get('game.invitation.tokens_per_user')) {
            return;
        }

        $this->invitePlayer->invite($user);

        $game->addInformation(_('Einladungslink wurde erstellt'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
