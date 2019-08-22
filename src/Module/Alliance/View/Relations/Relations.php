<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Relations;

use AccessViolation;
use Alliance;
use AllianceRelation;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class Relations implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_RELATIONS';

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();
        $allianceId = $alliance->getId();

        if (!$alliance->currentUserIsDiplomatic()) {
            throw new AccessViolation();
        }

        /**
         * @var AllianceRelation[] $result
         */
        $result = AllianceRelation::getList(sprintf('alliance_id = %1$d OR recipient = %1$d', $allianceId));

        $relations = [];
        foreach ($result as $key => $obj) {
            if ($obj->getRecipientId() == $$allianceId) {
                $obj->cycleOpponents();
            }
            $relations[$key] = $obj;
        }

        $game->setPageTitle(_('Diplomatie'));
        $game->appendNavigationPart(
            'alliance.php',
            _('Allianz')
        );
        $game->appendNavigationPart(
            'alliance.php?SHOW_RELATIONS=1',
            _('Diplomatie')
        );
        $game->setTemplateFile('html/alliancerelations.xhtml');
        $game->setTemplateVar('ALLIANCE_LIST', Alliance::getList());
        $game->setTemplateVar('RELATIONS', $relations);
    }
}
