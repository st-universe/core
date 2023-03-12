<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Overview;

use Stu\Component\Alliance\AllianceDescriptionRendererInterface;
use Stu\Component\Alliance\AllianceUserApplicationCheckerInterface;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceListItem;
use Stu\Module\Alliance\Lib\AllianceMemberWrapper;
use Stu\Module\Alliance\Lib\AllianceUiFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private AllianceRelationRepositoryInterface $allianceRelationRepository;

    private AllianceActionManagerInterface $allianceActionManager;

    private AllianceRepositoryInterface $allianceRepository;

    private AllianceUserApplicationCheckerInterface $allianceUserApplicationChecker;

    private AllianceDescriptionRendererInterface $allianceDescriptionRenderer;

    private AllianceUiFactoryInterface $allianceUiFactory;

    public function __construct(
        AllianceRelationRepositoryInterface $allianceRelationRepository,
        AllianceActionManagerInterface $allianceActionManager,
        AllianceRepositoryInterface $allianceRepository,
        AllianceUserApplicationCheckerInterface $allianceUserApplicationChecker,
        AllianceDescriptionRendererInterface $allianceDescriptionRenderer,
        AllianceUiFactoryInterface $allianceUiFactory
    ) {
        $this->allianceRelationRepository = $allianceRelationRepository;
        $this->allianceActionManager = $allianceActionManager;
        $this->allianceRepository = $allianceRepository;
        $this->allianceUserApplicationChecker = $allianceUserApplicationChecker;
        $this->allianceDescriptionRenderer = $allianceDescriptionRenderer;
        $this->allianceUiFactory = $allianceUiFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if ($alliance !== null) {
            $allianceId = $alliance->getId();
            $userId = $user->getId();

            $result = $this->allianceRelationRepository->getActiveByAlliance($allianceId);
            $userIsFounder = $alliance->getFounder()->getUserId() === $userId;

            $relations = [];
            foreach ($result as $key => $relation) {
                $relations[$key] = $this->allianceUiFactory->createAllianceRelationWrapper($alliance, $relation);
            }

            $game->setPageTitle(_('Allianz'));
            $game->setTemplateFile('html/alliancedetails.xhtml');

            $game->setTemplateVar('ALLIANCE', $alliance);
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
            $game->setTemplateVar('IS_IN_ALLIANCE', true);
            $game->setTemplateVar('CAN_LEAVE_ALLIANCE', !$userIsFounder);
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
                    fn (UserInterface $user): AllianceMemberWrapper => $this->allianceUiFactory->createAllianceMemberWrapper($user, $alliance)
                )
            );

            $game->appendNavigationPart(
                'alliance.php',
                _('Allianz')
            );
        } else {
            $game->appendNavigationPart('alliance.php?SHOW_LIST=1', _('Allianzliste'));
            $game->setTemplateFile('html/alliancelist.xhtml');
            $game->setPageTitle(_('Allianzliste'));
            $game->setTemplateVar(
                'ALLIANCE_LIST_OPEN',
                array_map(
                    fn (AllianceInterface $alliance): AllianceListItem => $this->allianceUiFactory->createAllianceListItem($alliance),
                    $this->allianceRepository->findByApplicationState(true)
                )
            );
            $game->setTemplateVar(
                'ALLIANCE_LIST_CLOSED',
                array_map(
                    fn (AllianceInterface $alliance): AllianceListItem => $this->allianceUiFactory->createAllianceListItem($alliance),
                    $this->allianceRepository->findByApplicationState(false)
                )
            );
        }
    }
}
