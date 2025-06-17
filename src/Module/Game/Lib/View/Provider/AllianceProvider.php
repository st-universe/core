<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Override;
use request;
use Stu\Component\Alliance\AllianceDescriptionRendererInterface;
use Stu\Component\Alliance\AllianceUserApplicationCheckerInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceListItem;
use Stu\Module\Alliance\Lib\AllianceMemberWrapper;
use Stu\Module\Alliance\Lib\AllianceUiFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class AllianceProvider implements ViewComponentProviderInterface
{
    public function __construct(private AllianceRelationRepositoryInterface $allianceRelationRepository, private AllianceActionManagerInterface $allianceActionManager, private AllianceRepositoryInterface $allianceRepository, private AllianceUserApplicationCheckerInterface $allianceUserApplicationChecker, private AllianceDescriptionRendererInterface $allianceDescriptionRenderer, private AllianceUiFactoryInterface $allianceUiFactory) {}

    #[Override]
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

        $game->addExecuteJS("initTranslations();", GameEnum::JS_EXECUTION_AFTER_RENDER);
    }

    private function setTemplateVariablesForAlliance(AllianceInterface $alliance, GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $allianceId = $alliance->getId();

        $result = $this->allianceRelationRepository->getActiveByAlliance($allianceId);
        $userIsFounder = $alliance->getFounder()->getUser() === $user;
        $isInAlliance = $alliance === $game->getUser()->getAlliance();


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
            'CAN_SIGNUP',
            $this->allianceUserApplicationChecker->mayApply($user, $alliance)
        );

        $game->setTemplateVar(
            'MEMBERS',
            $alliance->getMembers()->map(
                fn(UserInterface $user): AllianceMemberWrapper => $this->allianceUiFactory->createAllianceMemberWrapper($user, $alliance)
            )
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
                fn(AllianceInterface $alliance): AllianceListItem => $this->allianceUiFactory->createAllianceListItem($alliance),
                $this->allianceRepository->findByApplicationState(true)
            )
        );
        $game->setTemplateVar(
            'ALLIANCE_LIST_CLOSED',
            array_map(
                fn(AllianceInterface $alliance): AllianceListItem => $this->allianceUiFactory->createAllianceListItem($alliance),
                $this->allianceRepository->findByApplicationState(false)
            )
        );
    }
}
