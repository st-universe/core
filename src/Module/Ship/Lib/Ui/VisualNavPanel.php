<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use RuntimeException;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserLayerRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

class VisualNavPanel
{
    private ShipInterface $ship;

    private UserInterface $user;

    private LoggerUtilInterface $loggerUtil;

    private bool $isTachyonSystemActive;

    private bool $tachyonFresh;

    private ?StarSystemInterface $systemForSensorScan;

    /** @var null|array<int, VisualNavPanelRow> */
    private ?array $rows = null;

    /** @var null|array<array{value: int}> */
    private ?array $headRow = null;

    private ?float $viewport = null;

    private ?string $viewportPerColumn = null;

    private ?string $viewportForFont = null;

    private UserLayerRepositoryInterface $userLayerRepository;

    private UserMapRepositoryInterface $userMapRepository;

    private ShipRepositoryInterface $shipRepository;

    private ShipUiFactoryInterface $shipUiFactory;

    public function __construct(
        ShipUiFactoryInterface $shipUiFactory,
        UserLayerRepositoryInterface $userLayerRepository,
        UserMapRepositoryInterface $userMapRepository,
        ShipRepositoryInterface $shipRepository,
        ShipInterface $ship,
        UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        bool $isTachyonSystemActive,
        bool $tachyonFresh,
        ?StarSystemInterface $systemForSensorScan
    ) {
        $this->userLayerRepository = $userLayerRepository;
        $this->userMapRepository = $userMapRepository;
        $this->shipRepository = $shipRepository;
        $this->ship = $ship;
        $this->user = $user;
        $this->loggerUtil = $loggerUtil;
        $this->isTachyonSystemActive = $isTachyonSystemActive;
        $this->tachyonFresh = $tachyonFresh;
        $this->systemForSensorScan = $systemForSensorScan;
        $this->shipUiFactory = $shipUiFactory;
    }

    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    /**
     * @return array<int, VisualNavPanelRow>|null
     */
    public function getRows()
    {
        if ($this->rows === null) {
            $this->loadLSS();
        }
        return $this->rows;
    }

    /**
     * @return array<VisualNavPanelEntryData>
     */
    public function getOuterSystemResult(): array
    {
        $cx = $this->getShip()->getCX();
        $cy = $this->getShip()->getCY();
        $range = $this->getShip()->getSensorRange();

        $hasExploredLayer = false;

        $layer = $this->ship->getLayer();
        if ($layer !== null) {
            $layerId = $layer->getId();
            $hasSeenLayer = $this->user->hasSeen($layerId);
            $hasExploredLayer = $this->user->hasExplored($layerId);
            if (!$hasSeenLayer) {
                $userLayer = $this->userLayerRepository->prototype();
                $userLayer->setLayer($layer);
                $userLayer->setUser($this->user);

                $this->userLayerRepository->save($userLayer);

                $this->user->getUserLayers()->set($layerId, $userLayer);
            }
        }

        $layerId = $this->ship->getLayerId();
        if ($hasExploredLayer) {
            $this->userMapRepository->deleteMapFieldsForUser(
                $this->user->getId(),
                $layerId,
                $cx,
                $cy,
                $range
            );
        } else {
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
            $this->getShip()->getSubspaceState(),
            $this->user->getId()
        );
    }

    /**
     * @return array<VisualNavPanelEntryData>
     */
    private function getInnerSystemResult(): iterable
    {
        return $this->shipRepository->getSensorResultInnerSystem(
            $this->getShip(),
            $this->user->getId(),
            $this->systemForSensorScan
        );
    }

    public function loadLSS(): void
    {
        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        if ($this->systemForSensorScan === null && $this->showOuterMap()) {
            $result = $this->getOuterSystemResult();
        } else {
            $result = $this->getInnerSystemResult();
        }
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
        }

        $currentShip = $this->getShip();

        $cx = $currentShip->getPosX();
        $cy = $currentShip->getPosY();

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
                $navPanelRow = $this->shipUiFactory->createVisualNavPanelRow();
                $entry = $this->shipUiFactory->createVisualNavPanelEntry();
                $entry->row = $y;
                $entry->setCSSClass('th');
                $navPanelRow->addEntry($entry);

                $rows[$y] = $navPanelRow;
            }

            $navPanelRow = $rows[$y];
            $entry = $this->shipUiFactory->createVisualNavPanelEntry(
                $data,
                $currentShip->getLayer(),
                $this->isTachyonSystemActive,
                $this->tachyonFresh,
                $currentShip,
                $this->systemForSensorScan
            );
            if ($this->systemForSensorScan === null) {
                $entry->currentShipPosX = $cx;
                $entry->currentShipPosY = $cy;
                $entry->currentShipSysId = $currentShip->getSystemsId();
            }
            $navPanelRow->addEntry($entry);
        }
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            //$this->loggerUtil->log(sprintf("\tloadLSS-loop, seconds: %F", $endTime - $startTime));
        }

        $this->rows = $rows;
    }

    /**
     * @return array<array{value: int}>
     */
    public function getHeadRow()
    {
        if ($this->headRow === null) {
            $cx = $this->showOuterMap() ? $this->getShip()->getCx() : $this->getShip()->getPosX();
            $range = $this->getShip()->getSensorRange();

            if ($this->systemForSensorScan === null) {
                $min = $cx - $range;
                $max = $cx + $range;
            } else {
                $min = 1;
                $max = $this->systemForSensorScan->getMaxX();
            }

            $row = [];

            while ($min <= $max) {
                if ($min < 1) {
                    $min++;
                    continue;
                }
                if ($this->systemForSensorScan === null) {
                    if ($this->showOuterMap()) {
                        if ($this->getShip()->getLayer() === null) {
                            throw new RuntimeException('layer should not be null if outside of system');
                        }

                        if ($min > $this->getShip()->getLayer()->getWidth()) {
                            break;
                        }
                    }
                    if (!$this->showOuterMap()) {
                        if ($this->getShip()->getSystem() === null) {
                            throw new RuntimeException('system should not be null if inside of system');
                        }

                        if ($min > $this->getShip()->getSystem()->getMaxX()) {
                            break;
                        }
                    }
                }
                $row[]['value'] = $min;
                $min++;
            }

            $this->headRow = $row;
        }

        return $this->headRow;
    }

    private function getViewport(): float
    {
        if ($this->viewport === null) {
            $navPercentage = ($this->systemForSensorScan === null && $this->getShip()->isBase()) ? 50 : 33;
            $perColumn = $navPercentage / count($this->getHeadRow());
            $this->viewport = (float) min($perColumn, 1.7);
        }
        return $this->viewport;
    }

    public function getViewportPerColumn(): string
    {
        if ($this->viewportPerColumn === null) {
            $this->viewportPerColumn = number_format($this->getViewport(), 1);
        }
        return $this->viewportPerColumn;
    }

    public function getViewportForFont(): string
    {
        if ($this->viewportForFont === null) {
            $this->viewportForFont = number_format($this->getViewport() / 2, 1);
        }
        return $this->viewportForFont;
    }

    private function showOuterMap(): bool
    {
        return $this->ship->getSystem() === null
            || $this->ship->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SENSOR
            || $this->ship->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_BASE;
    }
}
