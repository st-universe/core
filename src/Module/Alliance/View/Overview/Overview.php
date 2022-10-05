<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Overview;

use Lib\AllianceMemberWrapper;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Lib\ParserWithImageInterface;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceListItem;
use Stu\Module\Alliance\Lib\AllianceListItemInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private AllianceRelationRepositoryInterface $allianceRelationRepository;

    private AllianceActionManagerInterface $allianceActionManager;

    private AllianceJobRepositoryInterface $allianceJobRepository;

    private AllianceRepositoryInterface $allianceRepository;

    private ParserWithImageInterface $parserWithImage;

    public function __construct(
        AllianceRelationRepositoryInterface $allianceRelationRepository,
        AllianceActionManagerInterface $allianceActionManager,
        AllianceJobRepositoryInterface $allianceJobRepository,
        AllianceRepositoryInterface $allianceRepository,
        ParserWithImageInterface $parserWithImage
    ) {
        $this->allianceRelationRepository = $allianceRelationRepository;
        $this->allianceActionManager = $allianceActionManager;
        $this->allianceJobRepository = $allianceJobRepository;
        $this->allianceRepository = $allianceRepository;
        $this->parserWithImage = $parserWithImage;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        if ($user->getAllianceId() > 0) {
            $alliance = $user->getAlliance();
            $allianceId = (int) $alliance->getId();
            $userId = $user->getId();

            $result = $this->allianceRelationRepository->getActiveByAlliance($allianceId);
            $resulthasvassal = $this->allianceRelationRepository->getActiveHasVassal($allianceId);
            $resultisvassal = $this->allianceRelationRepository->getActiveIsVassal($allianceId);
            $userIsFounder = $this->allianceJobRepository->getSingleResultByAllianceAndType(
                $allianceId,
                AllianceEnum::ALLIANCE_JOBS_FOUNDER
            )->getUserId() === $game->getUser()->getId();

            $relations = [];
            foreach ($result as $key => $obj) {
                $relations[$key] = [
                    'relation' => $obj,
                    'opponent' => $obj->getOpponentId() == $alliance->getId() ? $obj->getAlliance() : $obj->getOpponent()
                ];
            }

            $hasvassal = [];
            foreach ($resulthasvassal as $key => $obj) {
                $hasvassal[$key] = [
                    'relation' => $obj,
                    'opponent' => $obj->getOpponentId() == $alliance->getId() ? $obj->getAlliance() : $obj->getOpponent()
                ];
            }

            $isvassal = [];
            foreach ($resultisvassal as $key => $obj) {
                $isvassal[$key] = [
                    'relation' => $obj,
                    'opponent' => $obj->getOpponentId() == $alliance->getId() ? $obj->getAlliance() : $obj->getOpponent()
                ];
            }

            $replacementVars = $this->getReplacementVars($alliance);

            $description = str_replace(
                array_keys($replacementVars),
                array_values($replacementVars),
                $alliance->getDescription()
            );

            $parsedDescription = $this->parserWithImage->parse($description)->getAsHTML();

            $isInAlliance = $alliance->getId() == $game->getUser()->getAllianceId();

            $game->setPageTitle(_('Allianz'));
            $game->setTemplateFile('html/alliancedetails.xhtml');

            $game->setTemplateVar('ALLIANCE', $user->getAlliance());
            $game->setTemplateVar('ALLIANCE_RELATIONS', $relations);
            $game->setTemplateVar('ALLIANCE_HASVASSAL', $hasvassal);
            $game->setTemplateVar('ALLIANCE_ISVASSAL', $isvassal);
            $game->setTemplateVar('DESCRIPTION', $parsedDescription);
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
                $user->maySignup($allianceId)
            );

            $game->setTemplateVar(
                'MEMBERS',
                array_map(
                    function (UserInterface $user) use ($alliance): AllianceMemberWrapper {
                        return new AllianceMemberWrapper($user, $alliance);
                    },
                    $alliance->getMembers()->toArray()
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
                    function (AllianceInterface $alliance): AllianceListItemInterface {
                        return new AllianceListItem($alliance);
                    },
                    $this->allianceRepository->findByApplicationState(true)
                )
            );
            $game->setTemplateVar(
                'ALLIANCE_LIST_CLOSED',
                array_map(
                    function (AllianceInterface $alliance): AllianceListItemInterface {
                        return new AllianceListItem($alliance);
                    },
                    $this->allianceRepository->findByApplicationState(false)
                )
            );
        }
    }

    private function getReplacementVars(AllianceInterface $alliance): array
    {
        $replacementVars = [];
        $replacementVars['$ALLIANCE_HOMEPAGE_LINK'] = '<a href="' . $alliance->getHomepage() . '" target="_blank">' . _('Zur Allianz Homepage') . '</a>';
        $replacementVars['$ALLIANCE_BANNER'] = ($alliance->getAvatar() ? '<img src="' . $alliance->getFullAvatarpath() . '" />' : false);
        $replacementVars['$ALLIANCE_PRESIDENT'] = $alliance->getFounder()->getUser()->getUserName();
        $replacementVars['$ALLIANCE_VICEPRESIDENT'] = ($alliance->getSuccessor() ? $alliance->getSuccessor()->getUser()->getUserName() : _('Unbesetzt'));
        $replacementVars['$ALLIANCE_FOREIGNMINISTER'] = ($alliance->getDiplomatic() ? $alliance->getDiplomatic()->getUser()->getUserName() : _('Unbesetzt'));
        return $replacementVars;
    }
}