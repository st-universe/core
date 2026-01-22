<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use request;
use Stu\Component\Alliance\AllianceDescriptionRendererInterface;
use Stu\Component\Alliance\AllianceUserApplicationCheckerInterface;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Alliance\AllianceSettingsEnum;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Alliance\Lib\AllianceListItem;
use Stu\Module\Alliance\Lib\AllianceMemberWrapper;
use Stu\Module\Alliance\Lib\AllianceUiFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceSettings;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class AllianceProvider implements ViewComponentProviderInterface
{
    public function __construct(
        private AllianceRelationRepositoryInterface $allianceRelationRepository,
        private AllianceActionManagerInterface $allianceActionManager,
        private AllianceRepositoryInterface $allianceRepository,
        private AllianceUserApplicationCheckerInterface $allianceUserApplicationChecker,
        private AllianceDescriptionRendererInterface $allianceDescriptionRenderer,
        private AllianceUiFactoryInterface $allianceUiFactory,
        private AllianceJobManagerInterface $allianceJobManager
    ) {}

    #[\Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $alliance = null;
        if (request::has('id')) {
            $alliance = $this->allianceRepository->find(request::indInt('id'));
        }

        if ($alliance === null) {
            $alliance = $user->getAlliance();
        }

        $game->setTemplateVar('ALLIANCE', $alliance);

        if ($alliance === null || request::has('showlist')) {
            $this->setTemplateVariablesForAllianceList($game);
        } else {
            $this->setTemplateVariablesForAlliance($alliance, $game);
        }

        $game->addExecuteJS("initTranslations();", JavascriptExecutionTypeEnum::AFTER_RENDER);
    }

    private function setTemplateVariablesForAlliance(Alliance $alliance, GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $allianceId = $alliance->getId();

        $result = $this->allianceRelationRepository->getActiveByAlliance($allianceId);
        $userIsFounder = $this->allianceJobManager->hasUserFounderPermission($user, $alliance);
        $isInAlliance = $alliance->getId() === $game->getUser()->getAlliance()?->getId();
        $settings = $alliance->getSettings();

        $game->appendNavigationPart(sprintf(
            '%s?id=%d',
            ModuleEnum::ALLIANCE->getPhpPage(),
            $alliance->getId()
        ), _('Allianz anzeigen'));

        $relations = [];
        foreach ($result as $key => $relation) {
            $relations[$key] = $this->allianceUiFactory->createAllianceRelationWrapper($alliance, $relation);
        }

        $game->setTemplateVar('SHOW_ALLIANCE', $alliance);

        $game->setTemplateVar(
            'ALLIANCE_RELATIONS',
            $relations !== []
                ? $relations
                : null
        );
        $game->setTemplateVar(
            'DESCRIPTION',
            $this->allianceDescriptionRenderer->render($alliance)
        );
        $game->setTemplateVar('IS_IN_ALLIANCE', $isInAlliance);
        $game->setTemplateVar('CAN_LEAVE_ALLIANCE', $isInAlliance && !$userIsFounder);
        $game->setTemplateVar(
            'CAN_EDIT',
            $this->allianceActionManager->mayEdit($alliance, $user)
        );
        $game->setTemplateVar(
            'CAN_MANAGE_FOREIGN_RELATIONS',
            $this->allianceActionManager->mayManageForeignRelations($alliance, $user)
        );
        $game->setTemplateVar(
            'CAN_MANAGE_APPLICATIONS',
            $this->allianceActionManager->mayManageApplications($alliance, $user)
        );
        $game->setTemplateVar(
            'CAN_VIEW_ALLIANCE_STORAGE',
            $this->allianceActionManager->mayViewAllianceStorage($alliance, $user)
        );
        $game->setTemplateVar(
            'CAN_VIEW_ALLIANCE_HISTORY',
            $this->allianceActionManager->mayViewAllianceHistory($alliance, $user)
        );
        $game->setTemplateVar(
            'CAN_SIGNUP',
            $this->allianceUserApplicationChecker->mayApply($user, $alliance)
        );
        $game->setTemplateVar(
            'CAN_MANAGE_ALLIANCE',
            $this->allianceActionManager->mayManageAlliance($alliance, $user)
        );

        $membersWithJobs = $alliance->getMembers()->map(
            function (User $user) use ($alliance): array {
                $wrapper = $this->allianceUiFactory->createAllianceMemberWrapper($user, $alliance);

                $userJobs = [];
                foreach ($alliance->getJobs() as $job) {
                    if ($job->hasUser($user) && $job->getTitle() !== null) {
                        $userJobs[] = $job->getTitle();
                    }
                }

                return [
                    'wrapper' => $wrapper,
                    'jobs' => $userJobs
                ];
            }
        );

        $game->setTemplateVar('MEMBERS', $membersWithJobs);


        $founderJobs = [];
        $successorJobs = [];
        $diplomaticJobs = [];
        $otherJobs = [];

        foreach ($alliance->getJobs() as $job) {
            if (count($job->getUsers()) === 0 || $job->getSort() === null) {
                continue;
            }

            if ($job->hasPermission(AllianceJobPermissionEnum::FOUNDER->value)) {
                $founderJobs[] = $job;
            } elseif ($job->hasPermission(AllianceJobPermissionEnum::SUCCESSOR->value)) {
                $successorJobs[] = $job;
            } elseif ($job->hasPermission(AllianceJobPermissionEnum::DIPLOMATIC->value)) {
                $diplomaticJobs[] = $job;
            } elseif ($job->hasPermission(AllianceJobPermissionEnum::ALLIANCE_LEADERSHIP->value)) {
                $otherJobs[] = $job;
            }
        }

        usort($successorJobs, fn($a, $b) => $a->getSort() <=> $b->getSort());
        usort($diplomaticJobs, fn($a, $b) => $a->getSort() <=> $b->getSort());
        usort($otherJobs, fn($a, $b) => $a->getSort() <=> $b->getSort());

        $leadershipJobs = array_merge($founderJobs, $successorJobs, $diplomaticJobs, $otherJobs);

        $game->setTemplateVar('ALLIANCE_LEADERSHIP_JOBS', $leadershipJobs);

        $founderDescription = $settings->filter(
            function (AllianceSettings $setting): bool {
                return $setting->getSetting() === AllianceSettingsEnum::ALLIANCE_FOUNDER_DESCRIPTION;
            }
        )->first();

        $successorDescription = $settings->filter(
            function (AllianceSettings $setting): bool {
                return $setting->getSetting() === AllianceSettingsEnum::ALLIANCE_SUCCESSOR_DESCRIPTION;
            }
        )->first();

        $diplomatDescription = $settings->filter(
            function (AllianceSettings $setting): bool {
                return $setting->getSetting() === AllianceSettingsEnum::ALLIANCE_DIPLOMATIC_DESCRIPTION;
            }
        )->first();

        $game->setTemplateVar(
            'FOUNDER_DESCRIPTION',
            $founderDescription !== false ? $founderDescription->getValue() : 'Präsident'
        );

        $game->setTemplateVar(
            'SUCCESSOR_DESCRIPTION',
            $successorDescription !== false ? $successorDescription->getValue() : 'Vize-Präsident'
        );

        $game->setTemplateVar(
            'DIPLOMATIC_DESCRIPTION',
            $diplomatDescription !== false ? $diplomatDescription->getValue() : 'Außenminister'
        );
    }

    private function setTemplateVariablesForAllianceList(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(sprintf(
            '%s?showlist=1',
            ModuleEnum::ALLIANCE->getPhpPage()
        ), _('Allianzliste'));

        $game->setTemplateVar('SHOW_ALLIANCE_LIST', true);
        $game->setTemplateVar(
            'ALLIANCE_LIST_OPEN',
            array_map(
                fn(Alliance $alliance): AllianceListItem => $this->allianceUiFactory->createAllianceListItem($alliance),
                $this->allianceRepository->findByApplicationState(true)
            )
        );
        $game->setTemplateVar(
            'ALLIANCE_LIST_CLOSED',
            array_map(
                fn(Alliance $alliance): AllianceListItem => $this->allianceUiFactory->createAllianceListItem($alliance),
                $this->allianceRepository->findByApplicationState(false)
            )
        );
    }
}
