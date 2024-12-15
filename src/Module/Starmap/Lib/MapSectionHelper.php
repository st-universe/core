<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use RuntimeException;
use Stu\Component\Game\GameEnum;
use Stu\Component\Map\DirectionEnum;
use Stu\Component\Map\MapEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\LayerInterface;

final class MapSectionHelper
{
    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private StarmapUiFactoryInterface $starmapUiFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function setTemplateVars(
        GameControllerInterface $game,
        LayerInterface $layer,
        int $currentSection,
        bool $isMapEditor = false,
        ?DirectionEnum $direction = null
    ): int {
        //$this->loggerUtil->init('MSH', LoggerEnum::LEVEL_ERROR);

        $section = $this->getSection($currentSection, $direction, $layer);

        $xCoordinate = $this->getSectionX($section, $layer);
        $yCoordinate = $this->getSectionY($section, $layer);

        $this->loggerUtil->log(sprintf('section: %d, x: %d, y: %d', $section, $xCoordinate, $yCoordinate));

        $maxx = $xCoordinate * MapEnum::FIELDS_PER_SECTION;
        $minx = $maxx - MapEnum::FIELDS_PER_SECTION + 1;
        $maxy = $yCoordinate * MapEnum::FIELDS_PER_SECTION;
        $miny = $maxy - MapEnum::FIELDS_PER_SECTION + 1;

        $this->loggerUtil->log(sprintf('minx: %d, maxx: %d, miny: %d, maxy: %d', $minx, $maxx, $miny, $maxy));

        $fields = [];
        foreach (range($miny, $maxy) as $value) {
            if ($isMapEditor) {
                $fields[] = $this->starmapUiFactory->createYRow($layer, $value, $minx, $maxx, 0);
            } else {
                $fields[] = $this->starmapUiFactory->createUserYRow($game->getUser(), $layer, $value, $minx, $maxx);
            }
        }

        $game->setTemplateVar('HEAD_ROW', range($minx, $maxx));
        $game->setTemplateVar('MAP_FIELDS', $fields);
        $game->addExecuteJS(sprintf(
            'updateSectionAndLayer(%d, %d);',
            $section,
            $layer->getId()
        ), GameEnum::JS_EXECUTION_AJAX_UPDATE);

        $this->enableNavOptions($xCoordinate, $yCoordinate, $layer, $game);

        if ($isMapEditor) {
            $this->enablePreviewRows(
                $xCoordinate,
                $yCoordinate,
                $minx,
                $maxx,
                $miny,
                $maxy,
                $layer,
                $game
            );
        }

        return $section;
    }

    private function enablePreviewRows(
        int $xCoordinate,
        int $yCoordinate,
        int $minx,
        int $maxx,
        int $miny,
        int $maxy,
        LayerInterface $layer,
        GameControllerInterface $game
    ): void {
        if ($yCoordinate - 1 >= 1) {
            $game->setTemplateVar(
                'TOP_PREVIEW_ROW',
                $this->starmapUiFactory->createYRow($layer, $yCoordinate * MapEnum::FIELDS_PER_SECTION - MapEnum::FIELDS_PER_SECTION, $minx, $maxx, 0)->getFields()
            );
        }

        if ($yCoordinate * MapEnum::FIELDS_PER_SECTION + 1 <= $layer->getHeight()) {
            $game->setTemplateVar(
                'BOTTOM_PREVIEW_ROW',
                $this->starmapUiFactory->createYRow($layer, $yCoordinate * MapEnum::FIELDS_PER_SECTION + 1, $minx, $maxx, 0)->getFields()
            );
        }

        if ($xCoordinate - 1 >= 1) {
            $row = [];
            for ($i = $miny; $i <= $maxy; $i++) {
                $row[] = $this->starmapUiFactory->createYRow($layer, $i, $minx - 1, $minx - 1, 0);
            }

            $game->setTemplateVar(
                'LEFT_PREVIEW_ROW',
                $row
            );
        }

        if ($xCoordinate * MapEnum::FIELDS_PER_SECTION + 1 <= $layer->getWidth()) {
            $row = [];
            for ($i = $miny; $i <= $maxy; $i++) {
                $row[] = $this->starmapUiFactory->createYRow($layer, $i, $maxx + 1, $maxx + 1, 0);
            }

            $game->setTemplateVar(
                'RIGHT_PREVIEW_ROW',
                $row
            );
        }
    }

    private function enableNavOptions(
        int $xCoordinate,
        int $yCoordinate,
        LayerInterface $layer,
        GameControllerInterface $game
    ): void {
        $layerWidth = $layer->getWidth();
        $layerHeight = $layer->getHeight();

        $game->addExecuteJS(sprintf(
            'updateNavButtons(%b, %b, %b, %b);',
            $xCoordinate > 1,
            $xCoordinate * MapEnum::FIELDS_PER_SECTION < $layerWidth,
            $yCoordinate > 1,
            $yCoordinate * MapEnum::FIELDS_PER_SECTION < $layerHeight
        ), GameEnum::JS_EXECUTION_AJAX_UPDATE);
    }

    private function getSection(
        int $currentSection,
        ?DirectionEnum $direction,
        LayerInterface $layer
    ): int {

        $result = $currentSection;

        switch ($direction) {
            case DirectionEnum::LEFT:
                $result -= 1;
                break;
            case DirectionEnum::RIGHT:
                $result += 1;
                break;
            case DirectionEnum::TOP:
                $result -= $layer->getSectorsHorizontal();
                break;
            case DirectionEnum::BOTTOM:
                $result += $layer->getSectorsHorizontal();
                break;
        }

        if ($result < 1 || $result > $layer->getSectorCount()) {
            throw new RuntimeException('this should not happen');
        }

        return $result;
    }

    private function getSectionX(int $sectionId, LayerInterface $layer): int
    {
        $this->loggerUtil->log(sprintf('layerSectorsHorizontal: %d', $layer->getSectorsHorizontal()));

        $result = $sectionId % $layer->getSectorsHorizontal();

        return $result === 0 ? $layer->getSectorsHorizontal() : $result;
    }

    private function getSectionY(int $sectionId, LayerInterface $layer): int
    {
        return (int)ceil($sectionId / $layer->getSectorsHorizontal());
    }
}
