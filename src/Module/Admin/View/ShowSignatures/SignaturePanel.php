<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowSignatures;

use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class SignaturePanel
{
    private ?int $userId;
    private ?int $allyId;

    private $data = array();

    private LoggerUtilInterface $loggerUtil;

    function __construct(
        ?int $userId,
        ?int $allyId,
        &$entry = array(),
        LoggerUtilInterface $loggerUtil
    ) {
        $this->userId = $userId;
        $this->allyId = $allyId;
        $this->data = $entry;
        $this->loggerUtil = $loggerUtil;
    }

    private $rows = null;

    function getRows()
    {
        if ($this->rows === null) {
            $this->loadLSS();
        }
        return $this->rows;
    }

    function getOuterSystemResult()
    {
        // @todo refactor
        global $container;

        if ($this->userId !== null) {
            return $container->get(ShipRepositoryInterface::class)->getSignaturesOuterSystemOfUser(
                $this->data['minx'],
                $this->data['maxx'],
                $this->data['miny'],
                $this->data['maxy'],
                $this->userId
            );
        } else if ($this->allyId !== null) {
            return $container->get(ShipRepositoryInterface::class)->getSignaturesOuterSystemOfAlly(
                $this->data['minx'],
                $this->data['maxx'],
                $this->data['miny'],
                $this->data['maxy'],
                $this->allyId
            );
        }
    }

    function loadLSS()
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

        foreach ($result as $data) {
            if ($data['posy'] < 1) {
                continue;
            }
            if ($data['posy'] != $y) {
                $y = $data['posy'];
                $rows[$y] = new SignaturePanelRow;
                $entry = new SignaturePanelEntry();
                $entry->row = $y;
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

    private $headRow = null;

    function getHeadRow()
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


    private $viewport;
    private $viewportPerColumn;
    private $viewportForFont;

    private function getViewport()
    {
        if (!$this->viewportPerColumn) {
            $navPercentage = 100;
            $perColumn = $navPercentage / count($this->getHeadRow());
            $this->viewport = min($perColumn, 1.7);
        }
        return $this->viewport;
    }

    function getViewportPerColumn()
    {
        if (!$this->viewportPerColumn) {
            $this->viewportPerColumn = number_format($this->getViewport(), 1);
        }
        return $this->viewportPerColumn;
    }

    function getViewportForFont()
    {
        if (!$this->viewportForFont) {
            $this->viewportForFont = number_format($this->getViewport() / 2, 1);
        }
        return $this->viewportForFont;
    }
}
