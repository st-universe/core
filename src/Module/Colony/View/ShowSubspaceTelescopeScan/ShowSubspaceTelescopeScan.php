<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowSubspaceTelescopeScan;

use Override;
use request;
use RuntimeException;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\LocationRepositoryInterface;

final class ShowSubspaceTelescopeScan implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TELESCOPE_SCAN';

    private const int SCAN_BASE_COST = 20;
    private const int SCAN_VARIABEL_COST = 180;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private LocationRepositoryInterface $locationRepository,
        private ColonyRepositoryInterface $colonyRepository,
        private ColonyFunctionManagerInterface $colonyFunctionManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::getIntFatal('id'),
            $userId
        );

        if (!$this->colonyFunctionManager->hasFunction($colony, BuildingFunctionEnum::BUILDING_FUNCTION_SUBSPACE_TELESCOPE)) {
            return;
        }

        $cx = request::getIntFatal('x');
        $cy = request::getIntFatal('y');

        $scanCost = $this->calculateScanCost($colony, $cx, $cy);

        if ($scanCost > $colony->getEps()) {
            return;
        }

        $layer = $colony->getStarsystemMap()->getSystem()->getLayer();
        if ($layer === null) {
            throw new RuntimeException('this should not happen');
        }

        $game->setTemplateVar('INFOS', $this->locationRepository->getRumpCategoryInfo($layer, $cx, $cy));

        $colony->setEps($colony->getEps() - $scanCost);
        $this->colonyRepository->save($colony);

        $game->setPageTitle(sprintf(_('Subraum Teleskop Scan %d|%d'), $cx, $cy));
        $game->setMacroInAjaxWindow('html/colony/component/telescopeScan.twig');
    }

    private function calculateScanCost(ColonyInterface $colony, int $cx, int $cy): int
    {
        $layer = $colony->getSystem()->getLayer();

        $difX = abs($cx - $colony->getSystem()->getCx());
        $difY = abs($cy - $colony->getSystem()->getCy());
        $diagonal = (int)ceil(sqrt($difX * $difX + $difY * $difY));

        $maxDiagonal = (int)ceil(sqrt(($layer->getWidth() - 1) * ($layer->getWidth() - 1) + ($layer->getHeight() - 1) * ($layer->getHeight() - 1)));

        $neededEnergy = self::SCAN_BASE_COST + $diagonal / $maxDiagonal * self::SCAN_VARIABEL_COST;

        $this->loggerUtil->log(sprintf(
            'difX: %d, difY: %d, diagonal: %d, maxDiagonal: %d, neededEnergy: %d',
            $difX,
            $difY,
            $diagonal,
            $maxDiagonal,
            $neededEnergy
        ));

        return (int)round($neededEnergy);
    }
}
