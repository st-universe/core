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
        $playerId = request::getIntFatal('uid');

        $player = $this->userRepository->find($playerId);
        if ($player === null) {
            throw new ItemNotFoundException();
        }

        $user = $game->getUser();
        $userId = $user->getId();

        $this->profileVisitorRegistration->register($player, $user);

        $game->setTemplateVar('PROFILE', $player);
        $game->setTemplateVar(
            'DESCRIPTION',
            $this->parserWithImage->parse($player->getDescription())->getAsHTML()
        );
        $game->setTemplateVar(
            'IS_PROFILE_CURRENT_USER',
            $playerId === $userId
        );
        $game->setTemplateVar(
            'RPG_PLOTS',
            $this->rpgPlotMemberRepository->getByUser($player)
        );
        $game->setTemplateVar(
            'CONTACT',
            $this->contactRepository->getByUserAndOpponent(
                $userId,
                $playerId
            )
        );
        $game->setTemplateVar(
            'FRIENDS',
            $this->userRepository->getFriendsByUserAndAlliance(
                $player,
                $player->getAlliance()
            )
        );
        $game->setTemplateVar('CONTACT_LIST_MODES', ContactListModeEnum::cases());
    }
}
