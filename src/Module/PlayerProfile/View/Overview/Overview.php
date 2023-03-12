<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\View\Overview;

use Stu\Lib\ParserWithImageInterface;
use Stu\Module\Control\Exception\ItemNotFoundException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\PlayerProfile\Lib\ProfileVisitorRegistrationInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Renders the profile of a player
 */
final class Overview implements ViewControllerInterface
{
    private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository;

    private ContactRepositoryInterface $contactRepository;

    private UserRepositoryInterface $userRepository;

    private ParserWithImageInterface $parserWithImage;

    private OverviewRequestInterface $overviewRequest;

    private ProfileVisitorRegistrationInterface $profileVisitorRegistration;

    public function __construct(
        RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        ContactRepositoryInterface $contactRepository,
        UserRepositoryInterface $userRepository,
        ParserWithImageInterface $parserWithImage,
        OverviewRequestInterface $overviewRequest,
        ProfileVisitorRegistrationInterface $profileVisitorRegistration
    ) {
        $this->rpgPlotMemberRepository = $rpgPlotMemberRepository;
        $this->contactRepository = $contactRepository;
        $this->userRepository = $userRepository;
        $this->parserWithImage = $parserWithImage;
        $this->overviewRequest = $overviewRequest;
        $this->profileVisitorRegistration = $profileVisitorRegistration;
    }

    public function handle(GameControllerInterface $game): void
    {
        $playerId = $this->overviewRequest->getPlayerId();

        $player = $this->userRepository->find($playerId);
        if ($player === null) {
            throw new ItemNotFoundException();
        }

        $user = $game->getUser();
        $userId = $user->getId();

        $this->profileVisitorRegistration->register($player, $user);

        $game->appendNavigationPart(
            sprintf('userprofile.php?uid=%d', $playerId),
            'Spielerprofil'
        );

        $game->setPageTitle('Spielerprofil');
        $game->setTemplateFile('html/userprofile.xhtml');
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
    }
}
