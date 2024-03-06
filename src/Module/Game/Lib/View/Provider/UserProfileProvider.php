<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use request;
use Stu\Lib\ParserWithImageInterface;
use Stu\Module\Control\Exception\ItemNotFoundException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\View\Provider\ViewComponentProviderInterface;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\PlayerProfile\Lib\ProfileVisitorRegistrationInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Component\Game\GameEnum;

final class UserProfileProvider implements ViewComponentProviderInterface
{
    private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository;

    private ContactRepositoryInterface $contactRepository;

    private UserRepositoryInterface $userRepository;

    private ParserWithImageInterface $parserWithImage;

    private ProfileVisitorRegistrationInterface $profileVisitorRegistration;

    public function __construct(
        RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        ContactRepositoryInterface $contactRepository,
        UserRepositoryInterface $userRepository,
        ParserWithImageInterface $parserWithImage,
        ProfileVisitorRegistrationInterface $profileVisitorRegistration
    ) {
        $this->rpgPlotMemberRepository = $rpgPlotMemberRepository;
        $this->contactRepository = $contactRepository;
        $this->userRepository = $userRepository;
        $this->parserWithImage = $parserWithImage;
        $this->profileVisitorRegistration = $profileVisitorRegistration;
    }

    public function setTemplateVariables(GameControllerInterface $game): void
    {
        if (!request::has('uid')) {
            $user = $game->getUser();
        } else {
            $userId = request::getIntFatal('uid');

            $user = $this->userRepository->find($userId);
            if ($user === null) {
                throw new ItemNotFoundException();
            }
        }

        $visitor = $game->getUser();

        $this->profileVisitorRegistration->register($user, $visitor);

        $game->setTemplateVar('PROFILE', $user);
        $game->setTemplateVar(
            'DESCRIPTION',
            $this->parserWithImage->parse($user->getDescription())->getAsHTML()
        );
        $game->setTemplateVar(
            'IS_PROFILE_CURRENT_USER',
            $user === $visitor
        );
        $game->setTemplateVar(
            'RPG_PLOTS',
            $this->rpgPlotMemberRepository->getByUser($user)
        );
        $game->setTemplateVar(
            'CONTACT',
            $this->contactRepository->getByUserAndOpponent(
                $visitor->getId(),
                $user->getId()
            )
        );
        $game->setTemplateVar(
            'FRIENDS',
            $this->userRepository->getFriendsByUserAndAlliance(
                $user,
                $user->getAlliance()
            )
        );
        $game->setTemplateVar('CONTACT_LIST_MODES', ContactListModeEnum::cases());
        $game->addExecuteJS("initTranslations();", GameEnum::JS_EXECUTION_AFTER_RENDER);
    }
}
