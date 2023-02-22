<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

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
    /** @var ShipInterface */
    private $ship;

    /** @var UserInterface */
    private $user;

    private LoggerUtilInterface $loggerUtil;

    /** @var bool */
    private $isTachyonSystemActive;

    /** @var bool */
    private $tachyonFresh;

    /** @var StarSystemInterface|null */
    private $system;

    /** @var null|array<int, VisualNavPanelRow> */
    private $rows = null;

    /** @var null|array<array{value: int}> */
    private $headRow = null;

    /** @var null|int|float */
    private $viewport;

    /** @var null|string */
    private $viewportPerColumn;

    /** @var null|string */
    private $viewportForFont;

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
        StarSystemInterface $system = null
    ) {
        $this->userLayerRepository = $userLayerRepository;
        $this->userMapRepository = $userMapRepository;
        $this->shipRepository = $shipRepository;
        $this->ship = $ship;
        $this->user = $user;
        $this->loggerUtil = $loggerUtil;
        $this->isTachyonSystemActive = $isTachyonSystemActive;
        $this->tachyonFresh = $tachyonFresh;
        $this->system = $system;
        $this->shipUiFactory = $shipUiFactory;
    }

    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    /**
     * @return array<int, VisualNavPanelRow>
     */
    public function getRows()
    {
        if ($this->rows === null) {
            $this->loadLSS();
        }
        return $this->rows;
    }

    /**
     * @return iterable<array{
     *     posx: int,
     *     posy: int,
     *     sysid: int,
     *     shipcount: int,
     *     cloakcount: int,
     *     allycolor: string,
     *     usercolor: string,
     *     factioncolor: string,
     *     type: int,
     *     d1c?: int,
     *     d2c?: int,
     *     d3c?: int,
     *     d4c?: int
     * }>
     */
    public function getOuterSystemResult()
    {
        $cx = $this->getShip()->getCX();
        $cy = $this->getShip()->getCY();
        $range = $this->getShip()->getSensorRange();

        $layerId = $this->ship->getLayerId();
        $hasSeenLayer = $this->user->hasSeen($layerId);
        $hasExploredLayer = $this->user->hasExplored($layerId);

        if (!$hasSeenLayer) {
            $userLayer = $this->userLayerRepository->prototype();
            $userLayer->setLayer($this->ship->getLayer());
            $userLayer->setUser($this->user);

            $this->userLayerRepository->save($userLayer);

            $this->user->getUserLayers()->set($layerId, $userLayer);
        }

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
            $range,
            $this->getShip()->getSubspaceState(),
            $this->user->getId()
        );
    }

    /**
     * @return iterable<array{
     *     posx: int,
     *     posy: int,
     *     sysid: int,
     *     shipcount: int,
     *     cloakcount: int,
     *     shieldstate: bool,
     *     type: int,
     *     d1c?: int,
     *     d2c?: int,
     *     d3c?: int,
     *     d4c?: int
     * }>
     */
    public function getInnerSystemResult()
    {
        return $this->shipRepository->getSensorResultInnerSystem(
            $this->getShip(),
            $this->user->getId(),
            $this->system
        );
    }

    public function loadLSS(): void
    {
        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        if ($this->system === null && $this->showOuterMap()) {
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
        $y = 0;

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }

        foreach ($result as $data) {
            if ($data['posy'] < 1) {
                continue;
            }
            if ($data['posy'] != $y) {
                $y = $data['posy'];
                $rows[$y] = $this->shipUiFactory->createVisualNavPanelRow();
                $entry = $this->shipUiFactory->createVisualNavPanelEntry();
                $entry->row = $y;
                $entry->setCSSClass('th');
                $rows[$y]->addEntry($entry);
            }
            $entry = $this->shipUiFactory->createVisualNavPanelEntry(
                $data,
                $this->isTachyonSystemActive,
                $this->tachyonFresh,
                $currentShip,
                $this->system
            );
            if ($this->system === null) {
                $entry->currentShipPosX = $cx;
                $entry->currentShipPosY = $cy;
                $entry->currentShipSysId = $currentShip->getSystemsId();
            }
            $rows[$y]->addEntry($entry);
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
            $min = $this->system ? 1 : $cx - $range;
            $max = $this->system ? $this->system->getMaxX() : $cx + $range;
            while ($min <= $max) {
                if ($min < 1) {
                    $min++;
                    continue;
                }
                if ($this->system === null) {
                    if ($this->showOuterMap() && $min > $this->getShip()->getLayer()->getWidth()) {
                        break;
                    }
                    if (!$this->showOuterMap() && $min > $this->getShip()->getSystem()->getMaxX()) {
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

    /**
     * @return int|float
     */
    private function getViewport()
    {
        if (!$this->viewportPerColumn) {
            $navPercentage = ($this->system === null && $this->getShip()->isBase()) ? 50 : 33;
            $perColumn = $navPercentage / count($this->getHeadRow());
            $this->viewport = min($perColumn, 1.7);
        }
        return $this->viewport;
    }

    public function getViewportPerColumn(): string
    {
        if (!$this->viewportPerColumn) {
            $this->viewportPerColumn = number_format($this->getViewport(), 1);
        }
        return $this->viewportPerColumn;
    }

    public function getViewportForFont(): string
    {
        if (!$this->viewportForFont) {
            $this->viewportForFont = number_format($this->getViewport() / 2, 1);
        }
        return $this->viewportForFont;
    }

    private function showOuterMap(): bool
    {
        return $this->ship->getSystem() === null
            || $this->ship->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SENSOR;
    }
}
