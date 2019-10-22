<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\View\Overview;

use Noodlehaus\ConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\PlayerSetting\Lib\InvitationItem;
use Stu\Orm\Entity\UserInvitationInterface;
use Stu\Orm\Repository\UserInvitationRepositoryInterface;

final class Overview implements ViewControllerInterface
{

    private $userInvitationRepository;

    private $config;

    public function __construct(
        UserInvitationRepositoryInterface $userInvitationRepository,
        ConfigInterface $config
    ) {
        $this->userInvitationRepository = $userInvitationRepository;
        $this->config = $config;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $game->appendNavigationPart(
            'options.php',
            _('Optionen')
        );
        $game->setPageTitle(_('/ Optionen'));
        $game->setTemplateFile('html/options.xhtml');

        $invitations = $this->userInvitationRepository->getInvitationsByUser($user);

        $game->setTemplateVar('USER', $user);
        $game->setTemplateVar(
            'INVITATION_POSSIBLE',
            count($invitations) < $this->config->get('game.invitation.tokens_per_user')
        );
        $game->setTemplateVar(
            'INVITATIONS',
            array_map(
                function (UserInvitationInterface $userInvitation): InvitationItem {
                    return new InvitationItem($this->config, $userInvitation);
                },
                $invitations
            )
        );
    }
}
