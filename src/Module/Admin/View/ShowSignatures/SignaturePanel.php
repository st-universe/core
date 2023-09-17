<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowSignatures;

use RuntimeException;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Component\Map\MapEnum;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\SignaturePanelEntry;
use Stu\Lib\Map\VisualPanel\VisualPanelEntryData;
use Stu\Lib\Map\VisualPanel\VisualPanelRow;
use Stu\Lib\Map\VisualPanel\VisualPanelRowIndex;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class SignaturePanel extends AbstractVisualPanel
{
    private int $userId;
    private int $allyId;

    /** @var array{minx: int, maxx: int, miny: int, maxy: int} */
    private array $data;

    private ShipRepositoryInterface $shipRepository;

    private EncodedMapInterface $encodedMap;

    private LayerInterface $layer;

    /**
     * @param array{minx: int, maxx: int, miny: int, maxy: int} $entry
     */
    public function __construct(
        ShipRepositoryInterface $shipRepository,
        EncodedMapInterface $encodedMap,
        LayerInterface $layer,
        int $userId,
        int $allyId,
        LoggerUtilInterface $loggerUtil,
        array $entry
    ) {
        parent::__construct($loggerUtil);

        $this->shipRepository = $shipRepository;
        $this->encodedMap = $encodedMap;
        $this->layer = $layer;
        $this->userId = $userId;
        $this->allyId = $allyId;
        $this->data = $entry;
    }

    /**
     * @return array<VisualPanelEntryData>
     */
    private function getOuterSystemResult(): array
    {
        if ($this->userId !== 0) {
            return $this->shipRepository->getSignaturesOuterSystemOfUser(
                $this->data['minx'],
                $this->data['maxx'],
                $this->data['miny'],
                $this->data['maxy'],
                $this->layer->getId(),
                $this->userId
            );
        } elseif ($this->allyId !== 0) {
            return $this->shipRepository->getSignaturesOuterSystemOfAlly(
                $this->data['minx'],
                $this->data['maxx'],
                $this->data['miny'],
                $this->data['maxy'],
                $this->layer->getId(),
                $this->allyId
            );
        }

        throw new RuntimeException('either userId or allyId has to be set');
    }

    protected function loadLSS(): array
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
            if ($data->getPosY() < 1) {
                continue;
            }
            if ($data->getPosY() != $y) {
                $y = $data->getPosY();
                $rows[$y] = new VisualPanelRow();
                $rowIndex = new VisualPanelRowIndex($y, 'th');
                $rows[$y]->addEntry($rowIndex);
            }
            $entry = new SignaturePanelEntry(
                $data,
                $this->layer,
                $this->encodedMap
            );
            $rows[$y]->addEntry($entry);
        }
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            //$this->loggerUtil->log(sprintf("\tloadLSS-loop, seconds: %F", $endTime - $startTime));
        }

        return $rows;
    }

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

    protected function getPanelViewportPercentage(): int
    {
        return 100;
    }
}
