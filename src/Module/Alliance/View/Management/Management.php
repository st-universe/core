<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Management;

use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Alliance\Lib\AllianceUiFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;

final class Management implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_MANAGEMENT';

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AllianceJobManagerInterface $allianceJobManager,
        private AllianceUiFactoryInterface $allianceUiFactory,
        private StationRepositoryInterface $stationRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    private function getCurrentUserMinSort(Alliance $alliance, User $user): int
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

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $userId = $game->getUser()->getId();
        $user = $game->getUser();

        if ($alliance === null) {
            return;
        }

        if (
            !$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::SUCCESSOR)
            && !$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::DIPLOMATIC)
            && !$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::MANAGE_JOBS)
            && !$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::VIEW_COLONIES)
            && !$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::VIEW_MEMBER_DATA)
            && !$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::VIEW_SHIPS)
            && !$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::VIEW_ALLIANCE_STORAGE)
        ) {
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

        $stations = $this->stationRepository->getByAlliance($alliance->getId());
        $stationWrappers = $this->spacecraftWrapperFactory->wrapSpacecrafts($stations);

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

        $currentUserMinSort = $this->getCurrentUserMinSort($alliance, $user);
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
        $game->setTemplateVar('ALLIANCE_STATIONS', $stationWrappers);
        $game->setTemplateVar('MEMBER_LIST', $list);
    }
}
