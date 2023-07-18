<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowSignatures;

use Stu\Component\Map\MapEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class SignaturePanel
{
    private int $userId;
    private int $allyId;

    /** @var array{minx: int, maxx: int, miny: int, maxy: int} */
    private array $data;

    private LoggerUtilInterface $loggerUtil;

    private ShipRepositoryInterface $shipRepository;

    /**
     * @param array{minx: int, maxx: int, miny: int, maxy: int} $entry
     */
    public function __construct(
        ShipRepositoryInterface $shipRepository,
        int $userId,
        int $allyId,
        LoggerUtilInterface $loggerUtil,
        array $entry
    ) {
        $this->shipRepository = $shipRepository;
        $this->userId = $userId;
        $this->allyId = $allyId;
        $this->data = $entry;
        $this->loggerUtil = $loggerUtil;
    }

    /** @var array<SignaturePanelRow>|null */
    private ?array $rows = null;

    public function getRows()
    {
        if ($this->rows === null) {
            $this->loadLSS();
        }
        return $this->rows;
    }

    public function getOuterSystemResult()
    {
        if ($this->userId) {
            return $this->shipRepository->getSignaturesOuterSystemOfUser(
                $this->data['minx'],
                $this->data['maxx'],
                $this->data['miny'],
                $this->data['maxy'],
                MapEnum::LAYER_ID_CRAGGANMORE,
                $this->userId
            );
        } elseif ($this->allyId) {
            return $this->shipRepository->getSignaturesOuterSystemOfAlly(
                $this->data['minx'],
                $this->data['maxx'],
                $this->data['miny'],
                $this->data['maxy'],
                MapEnum::LAYER_ID_CRAGGANMORE,
                $this->allyId
            );
        }
    }

    public function loadLSS(): void
    {
        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        $result = $this->getOuterSystemResult();

        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            //$this->loggerUtil->log(sprintf("\tloadLSS-query, seconds: %F", $endTime - $startTime));
        }

        $y = 0;

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }

        $rows = [];

        foreach ($result as $data) {
            if ($data['posy'] < 1) {
                continue;
            }
            if ($data['posy'] != $y) {
                $y = $data['posy'];
                $rows[$y] = new SignaturePanelRow();
                $entry = new SignaturePanelEntry();
                $entry->setRow($y);
                $entry->setCSSClass('th');
                $rows[$y]->addEntry($entry);
            }
            $entry = new SignaturePanelEntry(
                $data
            );
            $rows[$y]->addEntry($entry);
        }
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            //$this->loggerUtil->log(sprintf("\tloadLSS-loop, seconds: %F", $endTime - $startTime));
        }

        $this->rows = $rows;
    }

    /** @var array<array{value: int}>|null */
    private ?array $headRow = null;

    /** @return array<array{value: int}>
     */
    public function getHeadRow(): array
    {
        if ($this->headRow === null) {
            $min = $this->data['minx'];
            $max = $this->data['maxx'];

            foreach (range($min, $max) as $x) {
                $row[]['value'] = $x;
            }

            $this->headRow = $row;
        }
        return $this->headRow;
    }


    private int|float|null $viewport = null;
    private ?string $viewportPerColumn = null;
    private ?string $viewportForFont = null;

    private function getViewport()
    {
        if (!$this->viewportPerColumn) {
            $navPercentage = 100;
            $perColumn = $navPercentage / count($this->getHeadRow());
            $this->viewport = min($perColumn, 1.7);
        }
        return $this->viewport;
    }

    public function getViewportPerColumn()
    {
        if (!$this->viewportPerColumn) {
            $this->viewportPerColumn = number_format($this->getViewport(), 1);
        }
        return $this->viewportPerColumn;
    }

    public function getViewportForFont()
    {
        if (!$this->viewportForFont) {
            $this->viewportForFont = number_format($this->getViewport() / 2, 1);
        }
        return $this->viewportForFont;
    }
}
