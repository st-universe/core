<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\AllianceDetails;

use Alliance;
use AllianceData;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class AllianceDetails implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ALLIANCE';

    private $allianceDetailsRequest;

    private $allianceRelationRepository;

    public function __construct(
        AllianceDetailsRequestInterface $allianceDetailsRequest,
        AllianceRelationRepositoryInterface $allianceRelationRepository
    ) {
        $this->allianceDetailsRequest = $allianceDetailsRequest;
        $this->allianceRelationRepository = $allianceRelationRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = new Alliance($this->allianceDetailsRequest->getAllianceId());

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

        $game->setPageTitle(_('Allianz anzeigen'));
        $game->setTemplateFile('html/alliancedetails.xhtml');

        $game->setTemplateVar('ALLIANCE', $alliance);
        $game->setTemplateVar('ALLIANCE_RELATIONS', $relations);
        $game->setTemplateVar('DESCRIPTION', $description);
        $game->setTemplateVar('IS_IN_ALLIANCE', $alliance->getId() == $game->getUser()->getAllianceId());

        if ($game->getUser()->getAllianceId() > 0) {
            $game->appendNavigationPart(
                'alliance.php',
                _('Allianz')
            );
        }
        $game->appendNavigationPart('alliance.php?SHOW_LIST=1', _('Allianzliste'));
        $game->appendNavigationPart(
            sprintf('alliance.php?SHOW_ALLIANCE=1&id=%d', $alliance->getId()),
            _('Allianz anzeigen')
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
