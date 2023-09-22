<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use RuntimeException;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\VisualPanelEntryData;
use Stu\Lib\Map\VisualPanel\VisualPanelRow;
use Stu\Lib\Map\VisualPanel\VisualPanelRowIndex;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

class VisualNavPanel extends AbstractVisualPanel
{
    private ShipInterface $currentShip;

    private UserInterface $user;

    private bool $isTachyonSystemActive;

    private bool $tachyonFresh;

    private UserMapRepositoryInterface $userMapRepository;

    private ShipRepositoryInterface $shipRepository;

    private ShipUiFactoryInterface $shipUiFactory;

    public function __construct(
        ShipUiFactoryInterface $shipUiFactory,
        UserMapRepositoryInterface $userMapRepository,
        ShipRepositoryInterface $shipRepository,
        ShipInterface $currentShip,
        UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        bool $isTachyonSystemActive,
        bool $tachyonFresh
    ) {
        parent::__construct($loggerUtil);

        $this->userMapRepository = $userMapRepository;
        $this->shipRepository = $shipRepository;
        $this->currentShip = $currentShip;
        $this->user = $user;
        $this->isTachyonSystemActive = $isTachyonSystemActive;
        $this->tachyonFresh = $tachyonFresh;
        $this->shipUiFactory = $shipUiFactory;
    }

    /**
     * @return array<VisualPanelEntryData>
     */
    private function getOuterSystemResult(): array
    {
        $cx = $this->currentShip->getCX();
        $cy = $this->currentShip->getCY();
        $range = $this->currentShip->getSensorRange();

        $layerId = $this->currentShip->getLayerId();
        if ($this->isUserMapActive($layerId)) {
            $this->userMapRepository->insertMapFieldsForUser(
                $this->user->getId(),
                $layerId,
                $cx,
                $cy,
                $range
            );
        }

        return $this->shipRepository->getSensorResultOuterSystem(
            $cx,
            $cy,
            $layerId,
            $range,
            $this->currentShip->getSubspaceState(),
            $this->user->getId()
        );
    }

    private function isUserMapActive(int $layerId): bool
    {
        if (!$this->user->hasColony()) {
            return false;
        }

        return !$this->user->hasExplored($layerId);
    }

    /**
     * @return array<VisualPanelEntryData>
     */
    private function getInnerSystemResult(): iterable
    {
        return $this->shipRepository->getSensorResultInnerSystem(
            $this->currentShip,
            $this->user->getId()
        );
    }

    protected function loadLSS(): array
    {
        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        if ($this->showOuterMap()) {
            $result = $this->getOuterSystemResult();
        } else {
            $result = $this->getInnerSystemResult();
        }
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
        }

        $currentShip = $this->currentShip;

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
            $entry = $this->shipUiFactory->createVisualNavPanelEntry(
                $data,
                $currentShip->getLayer(),
                $currentShip,
                $this->isTachyonSystemActive,
                $this->tachyonFresh
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
            $cx = $this->showOuterMap() ? $this->currentShip->getCx() : $this->currentShip->getPosX();
            $range = $this->currentShip->getSensorRange();

            $min = $cx - $range;
            $max = $cx + $range;

            $row = [];

            while ($min <= $max) {
                if ($min < 1) {
                    $min++;
                    continue;
                }
                if ($this->showOuterMap()) {
                    if ($this->currentShip->getLayer() === null) {
                        throw new RuntimeException('layer should not be null if outside of system');
                    }

                    if ($min > $this->currentShip->getLayer()->getWidth()) {
                        break;
                    }
                }
                if (!$this->showOuterMap()) {
                    if ($this->currentShip->getSystem() === null) {
                        throw new RuntimeException('system should not be null if inside of system');
                    }

                    if ($min > $this->currentShip->getSystem()->getMaxX()) {
                        break;
                    }
                }
                $row[]['value'] = $min;
                $min++;
            }

            $this->headRow = $row;
        }

        return $this->headRow;
    }

    protected function getPanelViewportPercentage(): int
    {
        return $this->currentShip->isBase() ? 50 : 33;
    }

    /**
     * @return array<VisualPanelEntryData>
     */
    private function getExtendedOuterSystemResult(): array
    {
        $cx = $this->currentShip->getCX();
        $cy = $this->currentShip->getCY();
        $range = $this->currentShip->getSensorRange() * 2;

        $layerId = $this->currentShip->getLayerId();

        return $this->shipRepository->getSensorResultOuterSystem(
            $cx,
            $cy,
            $layerId,
            $range,
            $this->currentShip->getSubspaceState(),
            $this->user->getId()
        );
    }

    public function getExtendedLSS(): array
    {
        $result = $this->getExtendedOuterSystemResult();

        $currentShip = $this->currentShip;
        $rows = [];

        foreach ($result as $data) {
            $y = $data->getPosY();

            if ($y < 1) {
                continue;
            }

            if (!array_key_exists($y, $rows)) {
                $navPanelRow = new VisualPanelRow();
                $rowIndex = new VisualPanelRowIndex($y, 'th');
                $navPanelRow->addEntry($rowIndex);
                $rows[$y] = $navPanelRow;
            }

            $navPanelRow = $rows[$y];
            $entry = $this->shipUiFactory->createVisualNavPanelEntry(
                $data,
                $currentShip->getLayer(),
                $currentShip,
                $this->isTachyonSystemActive,
                $this->tachyonFresh
            );
            $navPanelRow->addEntry($entry);
        }

        return $rows;
    }



    private function showOuterMap(): bool
    {
        return $this->currentShip->getSystem() === null
            || $this->currentShip->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SENSOR
            || $this->currentShip->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_BASE;
    }
}
