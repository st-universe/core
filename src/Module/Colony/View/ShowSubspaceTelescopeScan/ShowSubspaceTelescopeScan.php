<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowSubspaceTelescopeScan;

use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class ShowSubspaceTelescopeScan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TELESCOPE_SCAN';

    private const SCAN_BASE_COST = 20;
    private const SCAN_VARIABEL_COST = 180;

    private ColonyLoaderInterface $colonyLoader;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private LoggerUtilInterface $loggerUtil;

    private ColonyFunctionManagerInterface $colonyFunctionManager;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        ColonyRepositoryInterface $colonyRepository,
        ColonyFunctionManagerInterface $colonyFunctionManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->starSystemMapRepository = $starSystemMapRepository;
        $this->colonyRepository = $colonyRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->colonyFunctionManager = $colonyFunctionManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::getIntFatal('id'),
            $userId
        );

        if (!$this->colonyFunctionManager->hasFunction($colony, BuildingEnum::BUILDING_FUNCTION_SUBSPACE_TELESCOPE)) {
            return;
        }

        $cx = request::getIntFatal('cx');
        $cy = request::getIntFatal('cy');

        $scanCost = $this->calculateScanCost($colony, $cx, $cy);

        if ($scanCost > $colony->getEps()) {
            return;
        }

        $game->setTemplateVar('INFOS', $this->starSystemMapRepository->getRumpCategoryInfo($cx, $cy));

        $colony->setEps($colony->getEps() - $scanCost);
        $this->colonyRepository->save($colony);

        $game->setPageTitle(sprintf(_('Subraum Teleskop Scan %d|%d'), $cx, $cy));
        $game->setMacroInAjaxWindow('html/colonymacros.xhtml/telescopescan');
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
