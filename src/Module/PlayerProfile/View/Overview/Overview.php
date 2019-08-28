<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\View\Overview;

use RPGPlotMember;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use User;
use UserProfileVisitors;

final class Overview implements ViewControllerInterface
{

    private $overviewRequest;

    public function __construct(
        OverviewRequestInterface $overviewRequest
    ) {
        $this->overviewRequest = $overviewRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $profileId = $this->overviewRequest->getProfileId();
        $profile = new User($profileId);

        if ($profileId !== $userId && !UserProfileVisitors::hasVisit($profileId, $userId)) {
            UserProfileVisitors::registerVisit($profileId, $userId);
        }

        $game->appendNavigationPart(
            sprintf(
                'userprofile.php?uid=%d',
                $profile->getId()
            ),
            _('Siedlerprofil')
        );
        $game->setPageTitle(_('/ Siedlerprofi;e'));
        $game->setTemplateFile('html/userprofile.xhtml');
        $game->setTemplateVar('PROFILE', $profile);
        $game->setTemplateVar(
            'RPG_PLOTS',
            RPGPlotMember::getPlotsByUser($userId)
        );
    }
}