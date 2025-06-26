<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Override;
use request;
use Stu\Component\Game\GameEnum;
use Stu\Lib\ParserWithImageInterface;
use Stu\Module\Control\Exception\ItemNotFoundException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\PlayerProfile\Lib\ProfileVisitorRegistrationInterface;
use Stu\Orm\Entity\ColonyScan;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class UserProfileProvider implements ViewComponentProviderInterface
{
    public function __construct(
        private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        private ContactRepositoryInterface $contactRepository,
        private UserRepositoryInterface $userRepository,
        private ParserWithImageInterface $parserWithImage,
        private ProfileVisitorRegistrationInterface $profileVisitorRegistration
    ) {}

    #[Override]
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
        $game->setTemplateVar('HAS_TRANSLATION', $this->hasTranslation($user));
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

    private function hasTranslation(User $user): bool
    {
        $text = $user->getDescription();
        return strpos($text, '[translate]') !== false && strpos($text, '[/translate]') !== false;
    }

    /**
     * @return array<int, ColonyScan>
     */
    private function getColonyScanList(User $user, User $visitor): array
    {
        $alliance = $visitor->getAlliance();

        if ($alliance !== null) {
            $unfilteredScans = array_merge(...$alliance->getMembers()->map(fn(User $user) => $user->getColonyScans()->toArray()));
        } else {
            $unfilteredScans = $visitor->getColonyScans()->toArray();
        }

        $filteredScans = array_filter(
            $unfilteredScans,
            fn(ColonyScan $scan): bool => $scan->getColonyUserId() === $user->getId()
        );

        $scansByColony = [];
        foreach ($filteredScans as $scan) {
            $colonyId = $scan->getColony()->getId();
            if (!isset($scansByColony[$colonyId])) {
                $scansByColony[$colonyId] = [];
            }
            $scansByColony[$colonyId][] = $scan;
        }

        $latestScans = [];
        foreach ($scansByColony as $scans) {
            usort($scans, fn($a, $b): int => $b->getDate() <=> $a->getDate());
            $latestScans[] = $scans[0];
        }

        return $latestScans;
    }
}
