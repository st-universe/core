<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\Overview;

use Colfields;
use Colony;
use FieldTerraforming;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class Overview implements ViewControllerInterface
{

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colonyList = Colony::getListBy(sprintf('user_id = %d', $userId));
        $terraformingList = FieldTerraforming::getUnFinishedJobsByUser($userId);
        $buildingJobList = Colfields::getUnFinishedBuildingJobsByUser($userId);

        $game->appendNavigationPart(
            'colony.php',
            _('Kolonien')
        );
        $game->setPageTitle(_('/ Kolonien'));
        $game->setTemplateFile('html/colonylist.xhtml');

        $game->setTemplateVar(
            'COLONY_LIST',
            $colonyList
        );
        $game->setTemplateVar(
            'TERRAFORMING_LIST',
            $terraformingList
        );
        $game->setTemplateVar(
            'BUILDINGJOB_LIST',
            $buildingJobList
        );
    }
}
