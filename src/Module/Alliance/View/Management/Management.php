<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Management;

use Override;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceUiFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\UserRepositoryInterface;


final class Management implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_MANAGEMENT';

    public function __construct(private UserRepositoryInterface $userRepository, private AllianceActionManagerInterface $allianceActionManager, private AllianceUiFactoryInterface $allianceUiFactory) {}

    private function getCurrentUserMinSort(\Stu\Orm\Entity\Alliance $alliance, \Stu\Orm\Entity\User $user): int
    {
        $minSort = null;

        foreach ($alliance->getJobs() as $job) {
            if ($job->hasUser($user) && $job->getSort() !== null) {
                if ($minSort === null || $job->getSort() < $minSort) {
                    $minSort = $job->getSort();
                }
            }
        }

        return $minSort ?? PHP_INT_MAX;
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $userId = $game->getUser()->getId();

        if ($alliance === null) {
            return;
        }

        if (!$this->allianceActionManager->mayEdit($alliance, $game->getUser())) {
            return;
        }

        $list = [];
        foreach ($this->userRepository->getByAlliance($alliance) as $member) {
            $list[] = $this->allianceUiFactory->createManagementListItem(
                $alliance,
                $member,
                $userId
            );
        }

        $game->setPageTitle('Allianz verwalten');

        $game->setNavigation([
            [
                'url' => 'alliance.php',
                'title' => 'Allianz',
            ],
            [
                'url' => sprintf('alliance.php?%s=1', Management::VIEW_IDENTIFIER),
                'title' => 'Verwaltung'
            ],
        ]);

        $currentUserMinSort = $this->getCurrentUserMinSort($alliance, $game->getUser());
        $availableJobs = [];

        foreach ($alliance->getJobs() as $job) {
            if ($job->getSort() === null) {
                continue;
            }

            if ($job->getSort() >= $currentUserMinSort) {
                $availableJobs[] = $job;
            }
        }

        usort($availableJobs, fn($a, $b) => $a->getSort() <=> $b->getSort());

        $game->setViewTemplate('html/alliance/alliancemanagement.twig');
        $game->setTemplateVar('ALLIANCE', $alliance);
        $game->setTemplateVar('ALLIANCE_JOBS', $availableJobs);
        $game->setTemplateVar('MEMBER_LIST', $list);
        $game->setTemplateVar(
            'USER_IS_FOUNDER',
            in_array($userId, array_map(fn($j) => $j->getUserId(), $alliance->getJobsWithFounderPermission()))
        );
    }
}
