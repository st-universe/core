<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\EditSection;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;
use YRow;

final class EditSection implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_EDIT_MAP_SECTION';
    private const FIELDS_PER_SECTION = 20;

    private EditSectionRequestInterface $editSectionRequest;

    private LayerRepositoryInterface $layerRepository;

    private MapFieldTypeRepositoryInterface $mapFieldTypeRepository;

    public function __construct(
        EditSectionRequestInterface $editSectionRequest,
        LayerRepositoryInterface $layerRepository,
        MapFieldTypeRepositoryInterface $mapFieldTypeRepository
    ) {
        $this->editSectionRequest = $editSectionRequest;
        $this->layerRepository = $layerRepository;
        $this->mapFieldTypeRepository = $mapFieldTypeRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $xCoordinate = $this->editSectionRequest->getXCoordinate();
        $yCoordinate = $this->editSectionRequest->getYCoordinate();
        $section_id = $this->editSectionRequest->getSectionId();
        $layerId = request::getIntFatal('layerid');

        $maxx = $xCoordinate * self::FIELDS_PER_SECTION;
        $minx = $maxx - self::FIELDS_PER_SECTION + 1;
        $maxy = $yCoordinate * self::FIELDS_PER_SECTION;
        $miny = $maxy - self::FIELDS_PER_SECTION + 1;

        $fields = [];
        foreach (range($miny, $maxy) as $key => $value) {
            $fields[] = new YRow($value, $minx, $maxx);
        }

        $layer = $this->layerRepository->find($layerId);

        if ($yCoordinate - 1 >= 1) {
            $game->setTemplateVar(
                'TOP_PREVIEW_ROW',
                (new YRow($yCoordinate * self::FIELDS_PER_SECTION - self::FIELDS_PER_SECTION, $minx, $maxx))->getFields()
            );
        } else {
            $game->setTemplateVar('TOP_PREVIEW_ROW', false);
        }
        if ($yCoordinate * self::FIELDS_PER_SECTION + 1 <= $layer->getHeight()) {
            $game->setTemplateVar(
                'BOTTOM_PREVIEW_ROW',
                (new YRow($yCoordinate * self::FIELDS_PER_SECTION + 1, $minx, $maxx))->getFields()
            );
        } else {
            $game->setTemplateVar(
                'BOTTOM_PREVIEW_ROW',
                false
            );
        }
        if ($xCoordinate - 1 >= 1) {
            $row = [];
            for ($i = $miny; $i <= $maxy; $i++) {
                $row[] = new YRow($i, $minx - 1, $minx - 1);
            }

            $game->setTemplateVar(
                'LEFT_PREVIEW_ROW',
                $row
            );
        } else {
            $game->setTemplateVar(
                'LEFT_PREVIEW_ROW',
                false
            );
        }

        if ($xCoordinate * self::FIELDS_PER_SECTION + 1 <= $layer->getWidth()) {
            $row = [];
            for ($i = $miny; $i <= $maxy; $i++) {
                $row[] = new YRow($i, $maxx + 1, $maxx + 1);
            }

            $game->setTemplateVar(
                'RIGHT_PREVIEW_ROW',
                $row
            );
        } else {
            $game->setTemplateVar(
                'RIGHT_PREVIEW_ROW',
                false
            );
        }

        $possibleFieldTypes = ['row_0', 'row_1', 'row_2', 'row_3', 'row_4', 'row_5'];
        foreach ($this->mapFieldTypeRepository->findAll() as $key => $value) {
            if ($value->getIsSystem()) {
                continue;
            }
            $possibleFieldTypes['row_' . ($key % 6)][] = $value;
        }

        $game->setTemplateFile('html/admin/mapeditor_section.xhtml');
        $game->appendNavigationPart('/admin/?SHOW_MAP_EDITOR=1', _('Karteneditor'));
        $game->appendNavigationPart(
            sprintf(
                '/admin/?SHOW_EDIT_MAP_SECTION=1&x=%d&y=%d&sec=%d&layerid=%d',
                $xCoordinate,
                $yCoordinate,
                $section_id,
                $layerId
            ),
            sprintf(_('Sektion %d anzeigen'), $section_id)
        );
        $game->setPageTitle(_('Sektion anzeigen'));
        $game->setTemplateVar('POSSIBLE_FIELD_TYPES', $possibleFieldTypes);
        $game->setTemplateVar('FIELDS_PER_SECTION', static::FIELDS_PER_SECTION);
        $game->setTemplateVar('SECTION_ID', $section_id);
        $game->setTemplateVar('HEAD_ROW', range($minx, $maxx));
        $game->setTemplateVar('MAP_FIELDS', $fields);
        $game->setTemplateVar('HAS_NAV_LEFT', $xCoordinate > 1);
        $game->setTemplateVar('HAS_NAV_RIGHT', $xCoordinate * static::FIELDS_PER_SECTION < $layer->getWidth());
        $game->setTemplateVar('HAS_NAV_UP', $yCoordinate > 1);
        $game->setTemplateVar('HAS_NAV_DOWN', $yCoordinate * static::FIELDS_PER_SECTION < $layer->getHeight());
        $game->setTemplateVar(
            'NAV_UP',
            sprintf(
                '?%s=1&x=%d&y=%d&sec=%d&layerid=%d',
                static::VIEW_IDENTIFIER,
                $xCoordinate,
                $yCoordinate > 1 ? $yCoordinate - 1 : 1,
                $section_id - 6,
                $layerId
            )
        );
        $game->setTemplateVar(
            'NAV_DOWN',
            sprintf(
                "?%s=1&x=%d&y=%d&sec=%d&layerid=%d",
                static::VIEW_IDENTIFIER,
                $xCoordinate,
                $yCoordinate + 1 > $layer->getHeight() / self::FIELDS_PER_SECTION ? $yCoordinate : $yCoordinate + 1,
                $section_id + 6,
                $layerId
            )
        );
        $game->setTemplateVar(
            'NAV_LEFT',
            sprintf(
                "?%s=1&x=%d&y=%d&sec=%d&layerid=%d",
                static::VIEW_IDENTIFIER,
                $xCoordinate > 1 ? $xCoordinate - 1 : 1,
                $yCoordinate,
                $section_id - 1,
                $layerId
            )
        );
        $game->setTemplateVar(
            'NAV_RIGHT',
            sprintf(
                '?%s=1&x=%d&y=%d&sec=%d&layerid=%d',
                static::VIEW_IDENTIFIER,
                $xCoordinate + 1 > $layer->getWidth() / self::FIELDS_PER_SECTION ? $xCoordinate : $xCoordinate + 1,
                $yCoordinate,
                $section_id + 1,
                $layerId
            )
        );
    }
}
