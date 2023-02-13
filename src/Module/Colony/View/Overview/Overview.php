<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\Overview;

use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyListItemInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COLONY_LIST';

    private ColonyTerraformingRepositoryInterface $colonyTerraformingRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ModuleQueueRepositoryInterface $moduleQueueRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ColonyTerraformingRepositoryInterface $colonyTerraformingRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        ModuleQueueRepositoryInterface $moduleQueueRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->colonyTerraformingRepository = $colonyTerraformingRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->moduleQueueRepository = $moduleQueueRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colonyList = $game->getUser()->getColonies()->toArray();

        $game->appendNavigationPart(
            'colony.php',
            _('Kolonien')
        );
        $game->setPageTitle(_('/ Kolonien'));
        $game->setTemplateFile('html/colonylist.xhtml');

        // add production of colonies
        $productionOverview = [];
        foreach ($colonyList as $colony) {
            foreach ($colony->getProduction() as $prod) {
                if (!$prod->getCommodity()->isTradeable()) {
                    continue;
                }

                $commodityId = $prod->getCommodityId();

                if (array_key_exists($commodityId, $productionOverview)) {
                    $colonyProduction = $productionOverview[$commodityId];
                    $newProduction = $colonyProduction->getProduction() + $prod->getProduction();
                    $colonyProduction->setProduction($newProduction);
                } else {
                    $data = [];
                    $data['gc'] = $prod->getProduction();
                    $data['pc'] = 0;
                    $data['commodity_id'] = $commodityId;

                    $colonyProduction = $this->colonyLibFactory->createColonyProduction($data);
                    $productionOverview[$commodityId] = $colonyProduction;
                }
            }
        }
        // filter 0 values
        $productionOverview = array_filter(
            $productionOverview,
            function (ColonyProduction $prod): bool {
                return $prod->getProduction() > 0;
            }
        );

        usort(
            $productionOverview,
            function (ColonyProduction $a, ColonyProduction $b): int {
                if ($a->getCommodity()->getSort() == $b->getCommodity()->getSort()) {
                    return 0;
                }
                return ($a->getCommodity()->getSort() < $b->getCommodity()->getSort()) ? -1 : 1;
            }
        );


        $game->setTemplateVar(
            'COLONY_LIST',
            array_map(
                function (ColonyInterface $colony): ColonyListItemInterface {
                    return $this->colonyLibFactory->createColonyListItem($colony);
                },
                $colonyList
            )
        );
        $game->setTemplateVar(
            'PRODUCTION_LIST',
            $productionOverview
        );
        $game->setTemplateVar(
            'TERRAFORMING_LIST',
            $this->colonyTerraformingRepository->getByColony($colonyList)
        );
        $game->setTemplateVar(
            'BUILDINGJOB_LIST',
            $this->planetFieldRepository->getInConstructionByUser($userId)
        );
        $game->setTemplateVar(
            'MODULE_LIST',
            $this->fetchModuleList($userId)
        );
    }

    private function fetchModuleList(int $userId): array
    {
        return $this->moduleQueueRepository->getByUser($userId);
    }
}
