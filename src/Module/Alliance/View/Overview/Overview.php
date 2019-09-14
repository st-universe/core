<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Overview;

use Alliance;
use AllianceData;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private $allianceRelationRepository;

    public function __construct(
        AllianceRelationRepositoryInterface $allianceRelationRepository
    ) {
        $this->allianceRelationRepository = $allianceRelationRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        if ($user->getAllianceId() > 0) {
            $alliance = $user->getAlliance();

            $result = $this->allianceRelationRepository->getActiveByAlliance((int) $alliance->getId());

            $relations = [];
            foreach ($result as $key => $obj) {
                $relations[$key] = [
                    'relation' => $obj,
                    'opponent' => $obj->getRecipientId() == $alliance->getId() ? $obj->getAlliance() : $obj->getOpponent()
                ];
            }

            $replacementVars = $this->getReplacementVars($alliance);

            $description = str_replace(
                array_keys($replacementVars),
                array_values($replacementVars),
                $alliance->getDescription()
            );

            $isInAlliance = $alliance->getId() == $game->getUser()->getAllianceId();

            $game->setPageTitle(_('Allianz'));
            $game->setTemplateFile('html/alliancedetails.xhtml');

            $game->setTemplateVar('ALLIANCE', $user->getAlliance());
            $game->setTemplateVar('ALLIANCE_RELATIONS', $relations);
            $game->setTemplateVar('DESCRIPTION', $description);
            $game->setTemplateVar('IS_IN_ALLIANCE', $isInAlliance);
            $game->setTemplateVar('CAN_LEAVE_ALLIANCE', $isInAlliance && !$alliance->currentUserIsFounder());

            $game->appendNavigationPart(
                'alliance.php',
                _('Allianz')
            );
        } else {
            $game->appendNavigationPart('alliance.php?SHOW_LIST=1', _('Allianzliste'));
            $game->setTemplateFile('html/alliancelist.xhtml');
            $game->setPageTitle(_('Allianzliste'));
            $game->setTemplateVar('ALLIANCE_LIST', Alliance::getList());
        }
    }

    private function getReplacementVars(AllianceData $alliance): array
    {
        $replacementVars = [];
        $replacementVars['$ALLIANCE_HOMEPAGE_LINK'] = '<a href="' . $alliance->getHomepage() . '" target="_blank">' . _('Zur Allianz Homepage') . '</a>';
        $replacementVars['$ALLIANCE_BANNER'] = ($alliance->getAvatar() ? '<img src="' . $alliance->getFullAvatarpath() . '" />' : false);
        $replacementVars['$ALLIANCE_PRESIDENT'] = $alliance->getFounder()->getUser()->getName();
        $replacementVars['$ALLIANCE_VICEPRESIDENT'] = ($alliance->getSuccessor() ? $alliance->getSuccessor()->getUser()->getName() : _('Unbesetzt'));
        $replacementVars['$ALLIANCE_FOREIGNMINISTER'] = ($alliance->getDiplomatic() ? $alliance->getDiplomatic()->getUser()->getName() : _('Unbesetzt'));
        return $replacementVars;
    }
}
