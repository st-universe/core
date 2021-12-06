<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\View\Overview;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\UserProfileVisitorRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private OverviewRequestInterface $overviewRequest;

    private UserProfileVisitorRepositoryInterface $userProfileVisitorRepository;

    private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository;

    private ContactRepositoryInterface $contactRepository;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        OverviewRequestInterface $overviewRequest,
        UserProfileVisitorRepositoryInterface $userProfileVisitorRepository,
        RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        ContactRepositoryInterface $contactRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->overviewRequest = $overviewRequest;
        $this->userProfileVisitorRepository = $userProfileVisitorRepository;
        $this->rpgPlotMemberRepository = $rpgPlotMemberRepository;
        $this->contactRepository = $contactRepository;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $profile = $this->userRepository->find($this->overviewRequest->getProfileId());

        if ($profile === null) {
            $game->addInformation(_("Der Spieler existiert nicht!"));
            return;
        }

        $profileId = $profile->getId();

        if ($profileId !== $userId && $this->userProfileVisitorRepository->isVisitRegistered($profileId, $userId) === false) {
            $obj = $this->userProfileVisitorRepository->prototype()
                ->setProfileUser($profile)
                ->setUser($game->getUser())
                ->setDate(time());

            $this->userProfileVisitorRepository->save($obj);
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
        $game->setTemplateVar('IS_PROFILE_CURRENT_USER', $profile->getId() === $userId);
        $game->setTemplateVar(
            'RPG_PLOTS',
            $this->rpgPlotMemberRepository->getByUser($profileId)
        );
        $game->setTemplateVar(
            'CONTACT',
            $this->contactRepository->getByUserAndOpponent(
                $userId,
                $profileId
            )
        );
    }
}
