<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\View\Overview;

use request;
use Stu\Lib\ParserWithImageInterface;
use Stu\Module\Control\Exception\ItemNotFoundException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\UserProfileVisitorRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private UserProfileVisitorRepositoryInterface $userProfileVisitorRepository;

    private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository;

    private ContactRepositoryInterface $contactRepository;

    private UserRepositoryInterface $userRepository;

    private ParserWithImageInterface $parserWithImage;

    public function __construct(
        UserProfileVisitorRepositoryInterface $userProfileVisitorRepository,
        RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        ContactRepositoryInterface $contactRepository,
        UserRepositoryInterface $userRepository,
        ParserWithImageInterface $parserWithImage
    ) {
        $this->userProfileVisitorRepository = $userProfileVisitorRepository;
        $this->rpgPlotMemberRepository = $rpgPlotMemberRepository;
        $this->contactRepository = $contactRepository;
        $this->userRepository = $userRepository;
        $this->parserWithImage = $parserWithImage;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/.xhtml');
        $userId = $game->getUser()->getId();

        $userIdString = trim(request::getString('uid'));

        if (!is_numeric($userIdString) || ((int)$userIdString) < 1) {
            throw new ItemNotFoundException();
        }

        $profile = $this->userRepository->find((int) $userIdString);

        if ($profile === null) {
            throw new ItemNotFoundException();
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

        $parsedDescription = $this->parserWithImage->parse($profile->getDescription())->getAsHTML();

        $game->setPageTitle(_('/ Spielerprofile'));
        $game->setTemplateFile('html/userprofile.xhtml');
        $game->setTemplateVar('PROFILE', $profile);
        $game->setTemplateVar('DESCRIPTION', $parsedDescription);
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
