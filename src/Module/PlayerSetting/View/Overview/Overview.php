<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\View\Overview;

use Noodlehaus\ConfigInterface;
use Stu\Component\Index\News\NewsFactoryInterface;
use Stu\Component\Index\News\NewsItemInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\PlayerSetting\Lib\InvitationItem;
use Stu\Orm\Entity\NewsInterface;
use Stu\Orm\Entity\UserInvitationInterface;
use Stu\Orm\Repository\NewsRepositoryInterface;
use Stu\Orm\Repository\UserInvitationRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Overview implements ViewControllerInterface
{

    private UserInvitationRepositoryInterface $userInvitationRepository;

    private ConfigInterface $config;

    private NewsRepositoryInterface $newsRepository;

    private NewsFactoryInterface $newsFactory;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserInvitationRepositoryInterface $userInvitationRepository,
        ConfigInterface $config,
        NewsRepositoryInterface $newsRepository,
        NewsFactoryInterface $newsFactory,
        UserRepositoryInterface $userRepository
    ) {
        $this->userInvitationRepository = $userInvitationRepository;
        $this->config = $config;
        $this->newsRepository = $newsRepository;
        $this->newsFactory = $newsFactory;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        if ($user === null) {
            $game->setPageTitle(_('Star Trek Universe'));
            $game->setTemplateFile('html/index.xhtml');

            $game->setTemplateVar(
                'SYSTEM_NEWS',
                array_map(
                    function (NewsInterface $news): NewsItemInterface {
                        return $this->newsFactory->createNewsItem(
                            $news
                        );
                    },
                    $this->newsRepository->getRecent(5)
                )
            );

            return;
        }

        $game->appendNavigationPart(
            'options.php',
            _('Optionen')
        );
        $game->setPageTitle(_('/ Optionen'));
        $game->setTemplateFile('html/options.xhtml');

        $game->setTemplateVar('USER', $user);
        $game->setTemplateVar('WIKI', $this->config->get('wiki.base_url'));

        //invitation only possible if sms registration disabled
        if ($this->config->get('game.registration_via_sms')) {
            $game->setTemplateVar('INVITATION_POSSIBLE', false);
        } else {
            $invitations = $this->userInvitationRepository->getInvitationsByUser($user);
            $game->setTemplateVar(
                'INVITATION_POSSIBLE',
                count($invitations) < $this->config->get('game.invitation.tokens_per_user')
            );
        }
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
