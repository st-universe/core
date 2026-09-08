<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Doctrine\Common\Collections\Collection;
use request;
use Stu\Component\Alliance\AllianceDescriptionRendererInterface;
use Stu\Component\Alliance\AllianceSettingsEnum;
use Stu\Component\Alliance\AllianceUserApplicationCheckerInterface;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Alliance\Lib\AllianceListItem;
use Stu\Module\Alliance\Lib\AllianceMemberWrapper;
use Stu\Module\Alliance\Lib\AllianceRelationWrapper;
use Stu\Module\Alliance\Lib\AllianceUiFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\AllianceSettings;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;

final class AllianceProvider implements ViewComponentProviderInterface
{
    public function __construct(
        private RelationRepositoryInterface $allianceRelationRepository,
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

        $alliance ??= $user->getAlliance();

        $game->setTemplateVar('ALLIANCE', $alliance);

        if ($alliance === null || request::has('showlist')) {
            $this->setTemplateVariablesForAllianceList($game);
        } else {
            $this->setTemplateVariablesForAlliance($alliance, $game);
        }

        $game->addExecuteJS('initTranslations();', JavascriptExecutionTypeEnum::AFTER_RENDER);
    }

    private function setTemplateVariablesForAlliance(Alliance $alliance, GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userIsFounder = $this->allianceJobManager->hasUserPermission(
            $user,
            $alliance,
            AllianceJobPermissionEnum::FOUNDER
        );
        $isInAlliance = $alliance->getId() === $game->getUser()->getAlliance()?->getId();
        $settings = $alliance->getSettings();

        $game->appendNavigationPart(sprintf(
            '%s?id=%d',
            ModuleEnum::ALLIANCE->getPhpPage(),
            $alliance->getId()
        ), _('Allianz anzeigen'));

        $game->setTemplateVar('SHOW_ALLIANCE', $alliance);
        $game->setTemplateVar(
            'ALLIANCE_RELATIONS',
            $this->getAllianceRelations($alliance)
        );
        $game->setTemplateVar(
            'DESCRIPTION',
            $this->allianceDescriptionRenderer->render($alliance)
        );
        $game->setTemplateVar('IS_IN_ALLIANCE', $isInAlliance);
        $game->setTemplateVar('CAN_LEAVE_ALLIANCE', $isInAlliance && !$userIsFounder);
        $game->setTemplateVar(
            'CAN_SIGNUP',
            $this->allianceUserApplicationChecker->mayApply($user, $alliance)
        );

        $game->setTemplateVar('MEMBERS', $this->getMembersWithJobs($alliance));
        $game->setTemplateVar('ALLIANCE_LEADERSHIP_JOBS', $this->getLeadershipJobs($alliance));
        $this->setLeadershipDescriptions($game, $alliance->getSettings());
    }

    /** @return array<int, AllianceRelationWrapper>|null */
    private function getAllianceRelations(Alliance $alliance): ?array
    {
        $relations = [];
        foreach ($this->allianceRelationRepository->getActiveByAlliance($alliance->getId()) as $key => $relation) {
            $relations[$key] = $this->allianceUiFactory->createAllianceRelationWrapper($alliance, $relation);
        }

        return $relations !== [] ? $relations : null;
    }

    /** @return Collection<int, array{wrapper: AllianceMemberWrapper, jobs: list<string>}> */
    private function getMembersWithJobs(Alliance $alliance): Collection
    {
        return $alliance->getMembers()->map(
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
    }

    /** @return array<int, AllianceJob> */
    private function getLeadershipJobs(Alliance $alliance): array
    {
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

        usort($successorJobs, fn ($a, $b): int => $a->getSort() <=> $b->getSort());
        usort($diplomaticJobs, fn ($a, $b): int => $a->getSort() <=> $b->getSort());
        usort($otherJobs, fn ($a, $b): int => $a->getSort() <=> $b->getSort());

        $leadershipJobs = array_merge($founderJobs, $successorJobs, $diplomaticJobs, $otherJobs);

        return $leadershipJobs;
    }

    /** @param Collection<int, AllianceSettings> $settings */
    private function setLeadershipDescriptions(
        GameControllerInterface $game,
        Collection $settings
    ): void {
        $founderDescription = $settings
            ->filter(
                fn (AllianceSettings $setting): bool => (
                    $setting->getSetting() === AllianceSettingsEnum::ALLIANCE_FOUNDER_DESCRIPTION
                )
            )
            ->first();

        $successorDescription = $settings
            ->filter(
                fn (AllianceSettings $setting): bool => (
                    $setting->getSetting() === AllianceSettingsEnum::ALLIANCE_SUCCESSOR_DESCRIPTION
                )
            )
            ->first();

        $diplomatDescription = $settings
            ->filter(
                fn (AllianceSettings $setting): bool => (
                    $setting->getSetting() === AllianceSettingsEnum::ALLIANCE_DIPLOMATIC_DESCRIPTION
                )
            )
            ->first();

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
                fn (Alliance $alliance): AllianceListItem => $this->allianceUiFactory->createAllianceListItem(
                    $alliance
                ),
                $this->allianceRepository->findByApplicationState(true)
            )
        );
        $game->setTemplateVar(
            'ALLIANCE_LIST_CLOSED',
            array_map(
                fn (Alliance $alliance): AllianceListItem => $this->allianceUiFactory->createAllianceListItem(
                    $alliance
                ),
                $this->allianceRepository->findByApplicationState(false)
            )
        );
    }
}
