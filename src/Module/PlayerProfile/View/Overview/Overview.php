<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\View\Overview;

use RPGPlotMember;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use User;
use UserProfileVisitors;
use UserProfileVisitorsData;

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

        $profile = new User($this->overviewRequest->getProfileId());

        $profileId = $profile->getId();

        if ($profileId !== $userId && !UserProfileVisitors::hasVisit($profileId, $userId)) {
            $obj = new UserProfileVisitorsData();
            $obj->setRecipientId($profileId);
            $obj->setUserId($userId);
            $obj->setDate(time());
            $obj->save();
        }

        $game->appendNavigationPart(
            sprintf(
                'userprofile.php?uid=%d',
                $profile->getId()
            ),
            _('Spielerprofil')
        );
        $game->setPageTitle(_('/ Spielerprofile'));
        $game->setTemplateFile('html/userprofile.xhtml');
        $game->setTemplateVar('PROFILE', $profile);
        $game->setTemplateVar(
            'RPG_PLOTS',
            RPGPlotMember::getPlotsByUser($profileId)
        );
    }
}