<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\Overview;

use Colfields;
use Colony;
use ColonyData;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private $colonyTerraformingRepository;

    public function __construct(
        ColonyTerraformingRepositoryInterface $colonyTerraformingRepository
    ) {
        $this->colonyTerraformingRepository = $colonyTerraformingRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colonyList = Colony::getListBy(sprintf('user_id = %d', $userId));

        $colonyIdList = array_map(
            function (ColonyData $colony): int {
                return (int) $colony->getId();
            },
            $colonyList
        );

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
            $this->colonyTerraformingRepository->getByColony($colonyIdList)
        );
        $game->setTemplateVar(
            'BUILDINGJOB_LIST',
            $buildingJobList
        );
    }
}
