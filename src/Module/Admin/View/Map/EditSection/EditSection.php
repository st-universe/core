<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\EditSection;

use Stu\Component\Map\MapEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Starmap\Lib\StarmapUiFactoryInterface;
use Stu\Module\Starmap\View\ShowSection\ShowSectionRequestInterface;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;
use Stu\Orm\Repository\StarSystemTypeRepositoryInterface;

final class EditSection implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_EDIT_MAP_SECTION';

    private ShowSectionRequestInterface $request;

    private LayerRepositoryInterface $layerRepository;

    private MapFieldTypeRepositoryInterface $mapFieldTypeRepository;

    private StarmapUiFactoryInterface $starmapUiFactory;

    private StarSystemTypeRepositoryInterface $starSystemTypeRepository;

    public function __construct(
        ShowSectionRequestInterface $request,
        LayerRepositoryInterface $layerRepository,
        StarmapUiFactoryInterface $starmapUiFactory,
        MapFieldTypeRepositoryInterface $mapFieldTypeRepository,
        StarSystemTypeRepositoryInterface $starSystemTypeRepository
    ) {
        $this->request = $request;
        $this->layerRepository = $layerRepository;
        $this->mapFieldTypeRepository = $mapFieldTypeRepository;
        $this->starmapUiFactory = $starmapUiFactory;
        $this->starSystemTypeRepository = $starSystemTypeRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $layerId = $this->request->getLayerId();
        $layer = $this->layerRepository->find($layerId);

        $xCoordinate = $this->request->getXCoordinate($layer);
        $yCoordinate = $this->request->getYCoordinate($layer);
        $section_id = $this->request->getSectionId();

        $maxx = $xCoordinate * MapEnum::FIELDS_PER_SECTION;
        $minx = $maxx - MapEnum::FIELDS_PER_SECTION + 1;
        $maxy = $yCoordinate * MapEnum::FIELDS_PER_SECTION;
        $miny = $maxy - MapEnum::FIELDS_PER_SECTION + 1;

        $fields = [];
        foreach (range($miny, $maxy) as $value) {
            $fields[] = $this->starmapUiFactory->createYRow($layerId, $value, $minx, $maxx);
        }

        if ($yCoordinate - 1 >= 1) {
            $game->setTemplateVar(
                'TOP_PREVIEW_ROW',
                $this->starmapUiFactory->createYRow($layerId, $yCoordinate * MapEnum::FIELDS_PER_SECTION - MapEnum::FIELDS_PER_SECTION, $minx, $maxx)->getFields()
            );
        } else {
            $game->setTemplateVar('TOP_PREVIEW_ROW', false);
        }
        if ($yCoordinate * MapEnum::FIELDS_PER_SECTION + 1 <= $layer->getHeight()) {
            $game->setTemplateVar(
                'BOTTOM_PREVIEW_ROW',
                $this->starmapUiFactory->createYRow($layerId, $yCoordinate * MapEnum::FIELDS_PER_SECTION + 1, $minx, $maxx)->getFields()
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
                $row[] = $this->starmapUiFactory->createYRow($layerId, $i, $minx - 1, $minx - 1);
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

        if ($xCoordinate * MapEnum::FIELDS_PER_SECTION + 1 <= $layer->getWidth()) {
            $row = [];
            for ($i = $miny; $i <= $maxy; $i++) {
                $row[] = $this->starmapUiFactory->createYRow($layerId, $i, $maxx + 1, $maxx + 1);
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

        $possibleSystemTypes = ['row_0', 'row_1', 'row_2', 'row_3', 'row_4', 'row_5'];
        foreach ($this->starSystemTypeRepository->findAll() as $key => $value) {
            if (!$value->getIsGenerateable()) {
                continue;
            }
            $possibleSystemTypes['row_' . ($key % 6)][] = $value;
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
        $game->setTemplateVar('POSSIBLE_SYSTEM_TYPES', $possibleSystemTypes);
        $game->setTemplateVar('FIELDS_PER_SECTION', MapEnum::FIELDS_PER_SECTION);
        $game->setTemplateVar('SECTION_ID', $section_id);
        $game->setTemplateVar('HEAD_ROW', range($minx, $maxx));
        $game->setTemplateVar('MAP_FIELDS', $fields);
        $game->setTemplateVar('HAS_NAV_LEFT', $xCoordinate > 1);
        $game->setTemplateVar('HAS_NAV_RIGHT', $xCoordinate * MapEnum::FIELDS_PER_SECTION < $layer->getWidth());
        $game->setTemplateVar('HAS_NAV_UP', $yCoordinate > 1);
        $game->setTemplateVar('HAS_NAV_DOWN', $yCoordinate * MapEnum::FIELDS_PER_SECTION < $layer->getHeight());
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
                $yCoordinate + 1 > $layer->getHeight() / MapEnum::FIELDS_PER_SECTION ? $yCoordinate : $yCoordinate + 1,
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
                $xCoordinate + 1 > $layer->getWidth() / MapEnum::FIELDS_PER_SECTION ? $xCoordinate : $xCoordinate + 1,
                $yCoordinate,
                $section_id + 1,
                $layerId
            )
        );
    }
}
