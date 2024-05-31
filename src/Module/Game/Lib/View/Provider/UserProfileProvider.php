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
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\ColonyScanInterface;

final class UserProfileProvider implements ViewComponentProviderInterface
{
    public function __construct(
        private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        private ContactRepositoryInterface $contactRepository,
        private UserRepositoryInterface $userRepository,
        private ParserWithImageInterface $parserWithImage,
        private ProfileVisitorRegistrationInterface $profileVisitorRegistration
    ) {
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
        $game->setTemplateVar('COLONYSCANLIST', $this->getColonyScanList($user, $visitor));
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

    /**
     * @return array<ColonyScanInterface>
     */
    public function getColonyScanList(UserInterface $user, UserInterface $visitor): iterable
    {
        $alliance = $visitor->getAlliance();

        if ($alliance !== null) {
            $unfilteredScans = array_merge(...$alliance->getMembers()->map(fn (UserInterface $user) => $user->getColonyScans()->toArray()));
        } else {
            $unfilteredScans = $user->getColonyScans()->toArray();
        }


        return $this->filterByUser($unfilteredScans, $user);
    }

    /**
     * @param array<int, ColonyScanInterface> $colonyScans
     * 
     * @return array<int, ColonyScanInterface>
     */
    private function filterByUser(array $colonyScans, UserInterface $user): array
    {
        return array_filter(
            $colonyScans,
            fn (ColonyScanInterface $scan) => $scan->getColonyUserId() === $user->getId()
        );
    }
}
