<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\View\Overview;

use Noodlehaus\ConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\PlayerSetting\Lib\InvitationItem;
use Stu\Orm\Entity\UserInvitationInterface;
use Stu\Orm\Repository\NewsRepositoryInterface;
use Stu\Orm\Repository\UserInvitationRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Overview implements ViewControllerInterface
{

    private UserInvitationRepositoryInterface $userInvitationRepository;

    private ConfigInterface $config;

    private NewsRepositoryInterface $newsRepository;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserInvitationRepositoryInterface $userInvitationRepository,
        ConfigInterface $config,
        NewsRepositoryInterface $newsRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->userInvitationRepository = $userInvitationRepository;
        $this->config = $config;
        $this->newsRepository = $newsRepository;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        if ($user === null) {
            $game->setPageTitle(_('Star Trek Universe'));
            $game->setTemplateFile('html/index.xhtml');

            $game->setTemplateVar('SYSTEM_NEWS', $this->newsRepository->getRecent(5));

            return;
        }

        $game->appendNavigationPart(
            'options.php',
            _('Optionen')
        );
        $game->setPageTitle(_('/ Optionen'));
        $game->setTemplateFile('html/options.xhtml');

        $invitations = $this->userInvitationRepository->getInvitationsByUser($user);

        $game->setTemplateVar('USER', $user);
        $game->setTemplateVar('WIKI', $this->config->get('wiki.base_url'));
        $game->setTemplateVar(
            'INVITATION_POSSIBLE',
            count($invitations) < $this->config->get('game.invitation.tokens_per_user')
        );
        $game->setTemplateVar(
            'INVITATIONS',
            array_map(
                function (UserInvitationInterface $userInvitation): InvitationItem {
                    return new InvitationItem($this->config, $userInvitation, $this->userRepository);
                },
                $invitations
            )
        );
    }
}
