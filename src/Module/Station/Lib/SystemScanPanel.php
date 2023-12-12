<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;

use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\VisualPanelEntryData;
use Stu\Lib\Map\VisualPanel\VisualPanelRow;
use Stu\Lib\Map\VisualPanel\VisualPanelRowIndex;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class SystemScanPanel extends AbstractVisualPanel
{
    private ShipInterface $currentShip;

    private UserInterface $user;

    private StarSystemInterface $system;

    private ShipRepositoryInterface $shipRepository;

    private StationUiFactoryInterface $stationUiFactory;

    public function __construct(
        StationUiFactoryInterface $stationUiFactory,
        ShipRepositoryInterface $shipRepository,
        ShipInterface $currentShip,
        StarSystemInterface $system,
        UserInterface $user,
        LoggerUtilInterface $loggerUtil
    ) {
        parent::__construct($loggerUtil);

        $this->shipRepository = $shipRepository;
        $this->currentShip = $currentShip;
        $this->system = $system;
        $this->user = $user;
        $this->stationUiFactory = $stationUiFactory;
    }

    /**
     * @return array<VisualPanelEntryData>
     */
    private function getInnerSystemResult(): iterable
    {
        return $this->shipRepository->getSensorResultInnerSystem(
            $this->currentShip,
            $this->user->getId(),
            $this->system
        );
    }

    protected function loadLSS(): array
    {
        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }

        $result = $this->getInnerSystemResult();
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
        }

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }

        $rows = [];

        foreach ($result as $data) {
            $y = $data->getPosY();

            if ($y < 1) {
                continue;
            }

            //create new row if y changed
            if (!array_key_exists($y, $rows)) {
                $navPanelRow = new VisualPanelRow();
                $rowIndex = new VisualPanelRowIndex($y, 'th');
                $navPanelRow->addEntry($rowIndex);

                $rows[$y] = $navPanelRow;
            }

            $navPanelRow = $rows[$y];
            $entry = $this->stationUiFactory->createSystemScanPanelEntry(
                $data,
                $this->system,
            );
            $navPanelRow->addEntry($entry);
        }
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            //$this->loggerUtil->log(sprintf("\tloadLSS-loop, seconds: %F", $endTime - $startTime));
        }

        return $rows;
    }

    /**
     * @return array<array{value: int}>
     */
    public function getHeadRow(): array
    {
        if ($this->headRow === null) {

            $row = [];

            foreach (range(1, $this->system->getMaxX()) as $x) {
                $row[]['value'] = $x;
            }

            $this->headRow = $row;
        }

        return $this->headRow;
    }

    protected function getPanelViewportPercentage(): int
    {
        return 33;
    }
}
