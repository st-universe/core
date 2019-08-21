<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\AllianceDetails;

use Alliance;
use AllianceData;
use AllianceRelation;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class AllianceDetails implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ALLIANCE';

    private $allianceDetailsRequest;

    public function __construct(
        AllianceDetailsRequestInterface $allianceDetailsRequest
    ) {
        $this->allianceDetailsRequest = $allianceDetailsRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = new Alliance($this->allianceDetailsRequest->getAllianceId());

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

        $game->setPageTitle(_('Allianz anzeigen'));
        $game->setTemplateFile('html/alliancedetails.xhtml');

        $game->setTemplateVar('ALLIANCE', $alliance);
        $game->setTemplateVar('ALLIANCE_RELATIONS', $relations);
        $game->setTemplateVar('DESCRIPTION', $description);

        $game->appendNavigationPart(
            sprintf('alliance.php?ALLIANCE_DETAILS=1&id=%d', $alliance->getId()),
            _('Allianzschirm')
        );
    }

    /**
     * @todo refactor - duplicate of Overview::getReplacementVars
     */
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
