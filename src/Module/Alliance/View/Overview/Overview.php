<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Overview;

use Alliance;
use AllianceData;
use AllianceRelation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class Overview implements ViewControllerInterface
{

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        if ($user->getAllianceId() > 0) {
            $alliance = $user->getAlliance();

            /**
             * @var AllianceRelation[] $result
             */
            $result = AllianceRelation::getList(sprintf(
                'date>0 AND (recipient = %1$d OR alliance_id = %1$d)',
                $alliance->getId()
            ));
            $relations = [];
            foreach ($result as $key => $obj) {
                if ($obj->getRecipientId() == $alliance->getId()) {
                    $obj->cycleOpponents();
                }
                $relations[$key] = $obj;
            }

            $replacementVars = $this->getReplacementVars($alliance);

            $description = str_replace(
                array_keys($replacementVars),
                array_values($replacementVars),
                $alliance->getDescription()
            );

            $game->setPageTitle(_('Allianz'));
            $game->setTemplateFile('html/alliancedetails.xhtml');

            $game->setTemplateVar('ALLIANCE', $user->getAlliance());
            $game->setTemplateVar('ALLIANCE_RELATIONS', $relations);
            $game->setTemplateVar('DESCRIPTION', $description);

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
        $replacementVars['$ALLIANCE_HOMEPAGE_LINK'] = '<a href="' . $alliance->getHomepageDisplay() . '" target="_blank">' . _('Zur Allianz Homepage') . '</a>';
        $replacementVars['$ALLIANCE_BANNER'] = ($alliance->getAvatar() ? '<img src="' . $alliance->getFullAvatarpath() . '" />' : false);
        $replacementVars['$ALLIANCE_PRESIDENT'] = $alliance->getFounder()->getUser()->getName();
        $replacementVars['$ALLIANCE_VICEPRESIDENT'] = ($alliance->getSuccessor() ? $alliance->getSuccessor()->getUser()->getName() : _('Unbesetzt'));
        $replacementVars['$ALLIANCE_FOREIGNMINISTER'] = ($alliance->getDiplomatic() ? $alliance->getDiplomatic()->getUser()->getName() : _('Unbesetzt'));
        return $replacementVars;
    }
}
