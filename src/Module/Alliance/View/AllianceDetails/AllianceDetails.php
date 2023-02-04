<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\AllianceDetails;

use Stu\Component\Alliance\AllianceDescriptionRendererInterface;
use Stu\Component\Alliance\AllianceUserApplicationCheckerInterface;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceMemberWrapper;
use Stu\Module\Alliance\Lib\AllianceUiFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class AllianceDetails implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ALLIANCE';

    private AllianceDetailsRequestInterface $allianceDetailsRequest;

    private AllianceRelationRepositoryInterface $allianceRelationRepository;

    private AllianceActionManagerInterface $allianceActionManager;

    private AllianceRepositoryInterface $allianceRepository;

    private AllianceUserApplicationCheckerInterface $allianceUserApplicationChecker;

    private AllianceDescriptionRendererInterface $allianceDescriptionRenderer;

    private AllianceUiFactoryInterface $allianceUiFactory;

    public function __construct(
        AllianceDetailsRequestInterface $allianceDetailsRequest,
        AllianceRelationRepositoryInterface $allianceRelationRepository,
        AllianceActionManagerInterface $allianceActionManager,
        AllianceRepositoryInterface $allianceRepository,
        AllianceUserApplicationCheckerInterface $allianceUserApplicationChecker,
        AllianceDescriptionRendererInterface $allianceDescriptionRenderer,
        AllianceUiFactoryInterface $allianceUiFactory
    ) {
        $this->allianceDetailsRequest = $allianceDetailsRequest;
        $this->allianceRelationRepository = $allianceRelationRepository;
        $this->allianceActionManager = $allianceActionManager;
        $this->allianceRepository = $allianceRepository;
        $this->allianceUserApplicationChecker = $allianceUserApplicationChecker;
        $this->allianceDescriptionRenderer = $allianceDescriptionRenderer;
        $this->allianceUiFactory = $allianceUiFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $this->allianceRepository->find($this->allianceDetailsRequest->getAllianceId());
        if ($alliance === null) {
            return;
        }

        $user = $game->getUser();

        $allianceId = $alliance->getId();
        $userId = $user->getId();

        $result = $this->allianceRelationRepository->getActiveByAlliance($allianceId);
        $userIsFounder = $alliance->getFounder()->getUserId() === $userId;

        $relations = [];
        foreach ($result as $key => $relation) {
            $relations[$key] = $this->allianceUiFactory->createAllianceRelationWrapper($alliance, $relation);
        }

        $isInAlliance = $alliance->getId() == $game->getUser()->getAllianceId();

        $game->setPageTitle(_('Allianz anzeigen'));
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
        $game->setTemplateVar('IS_IN_ALLIANCE', $isInAlliance);
        $game->setTemplateVar('CAN_LEAVE_ALLIANCE', $isInAlliance && !$userIsFounder);
        $game->setTemplateVar(
            'CAN_EDIT',
            $this->allianceActionManager->mayEdit($allianceId, $userId)
        );
        $game->setTemplateVar(
            'CAN_MANAGE_FOREIGN_RELATIONS',
            $this->allianceActionManager->mayManageForeignRelations($allianceId, $userId)
        );
        $game->setTemplateVar(
            'CAN_SIGNUP',
            $this->allianceUserApplicationChecker->mayApply($user, $alliance)
        );
        $game->setTemplateVar(
            'MEMBERS',
            $alliance->getMembers()->map(
                fn (UserInterface $user): AllianceMemberWrapper => $this->allianceUiFactory->createAllianceMemberWrapper($user, $alliance),
            )
        );

        if ($game->getUser()->getAllianceId() > 0) {
            $game->appendNavigationPart(
                'alliance.php',
                'Allianz'
            );
        }
        $game->appendNavigationPart('alliance.php?SHOW_LIST=1', _('Allianzliste'));
        $game->appendNavigationPart(
            sprintf('alliance.php?SHOW_ALLIANCE=1&id=%d', $alliance->getId()),
            'Allianz anzeigen'
        );
    }
}
