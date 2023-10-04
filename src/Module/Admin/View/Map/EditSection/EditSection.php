<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\EditSection;

use RuntimeException;
use Stu\Component\Game\GameEnum;
use Stu\Component\Map\MapEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Starmap\Lib\StarmapUiFactoryInterface;
use Stu\Module\Starmap\View\ShowSection\ShowSectionRequestInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;
use Stu\Orm\Repository\StarSystemTypeRepositoryInterface;
use Stu\Orm\Repository\MapRegionRepositoryInterface;
use Stu\Orm\Repository\MapBorderTypeRepositoryInterface;

final class EditSection implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_EDIT_MAP_SECTION';

    private ShowSectionRequestInterface $request;

    private LayerRepositoryInterface $layerRepository;

    private MapFieldTypeRepositoryInterface $mapFieldTypeRepository;

    private MapRegionRepositoryInterface $mapRegionRepository;

    private MapBorderTypeRepositoryInterface $mapBorderTypeRepository;

    private StarmapUiFactoryInterface $starmapUiFactory;

    private StarSystemTypeRepositoryInterface $starSystemTypeRepository;

    public function __construct(
        ShowSectionRequestInterface $request,
        LayerRepositoryInterface $layerRepository,
        StarmapUiFactoryInterface $starmapUiFactory,
        MapRegionRepositoryInterface $mapRegionRepository,
        MapBorderTypeRepositoryInterface $mapBorderTypeRepository,
        MapFieldTypeRepositoryInterface $mapFieldTypeRepository,
        StarSystemTypeRepositoryInterface $starSystemTypeRepository
    ) {
        $this->request = $request;
        $this->layerRepository = $layerRepository;
        $this->mapFieldTypeRepository = $mapFieldTypeRepository;
        $this->starmapUiFactory = $starmapUiFactory;
        $this->mapBorderTypeRepository = $mapBorderTypeRepository;
        $this->mapRegionRepository = $mapRegionRepository;
        $this->starSystemTypeRepository = $starSystemTypeRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $layerId = $this->request->getLayerId();
        $layer = $this->layerRepository->find($layerId);
        if ($layer === null) {
            throw new RuntimeException(sprintf('layerId %d does not exist', $layerId));
        }

        $section_id = $this->request->getSection();

        $possibleFieldTypes = ['row_0' => [], 'row_1' => [], 'row_2' => [], 'row_3' => [], 'row_4' => [], 'row_5' => []];
        foreach ($this->mapFieldTypeRepository->findAll() as $key => $value) {
            if ($value->getIsSystem()) {
                continue;
            }
            $possibleFieldTypes['row_' . ($key % 6)][] = $value;
        }

        $possibleSystemTypes = ['row_0' => [], 'row_1' => [], 'row_2' => [], 'row_3' => [], 'row_4' => [], 'row_5' => []];
        foreach ($this->starSystemTypeRepository->findAll() as $key => $value) {
            if (!$value->getIsGenerateable()) {
                continue;
            }
            $possibleSystemTypes['row_' . ($key % 6)][] = $value;
        }

        $possibleRegion = ['row_0'];
        foreach ($this->mapRegionRepository->findAll() as $key => $value) {
            if ($value->getId() < 100 && $value->getDatabaseEntry() === null) {
                continue;
            }
            $possibleRegion['row_' . ($key % 1)][] = $value;
        }

        $possibleAdminRegion = ['row_0'];
        foreach ($this->mapRegionRepository->findAll() as $key => $value) {
            if ($value->getId() >= 100) {
                continue;
            }
            $possibleAdminRegion['row_' . ($key % 1)][] = $value;
        }

        $possibleBorder = ['row_0'];
        foreach ($this->mapBorderTypeRepository->findAll() as $key => $value) {
            $possibleBorder['row_' . ($key % 1)][] = $value;
        }

        $game->setTemplateFile('html/admin/mapeditor_section.twig', true);
        $game->appendNavigationPart('/admin/?SHOW_MAP_EDITOR=1', _('Karteneditor'));
        $game->appendNavigationPart(
            sprintf(
                '/admin/?SHOW_EDIT_MAP_SECTION=1&section=%d&layerid=%d',
                $section_id,
                $layerId
            ),
            sprintf(_('Sektion %d anzeigen'), $section_id)
        );
        $game->setPageTitle(_('Sektion anzeigen'));
        $game->setTemplateVar('POSSIBLE_FIELD_TYPES', $possibleFieldTypes);
        $game->setTemplateVar('POSSIBLE_SYSTEM_TYPES', $possibleSystemTypes);
        $game->setTemplateVar('POSSIBLE_REGION', $possibleRegion);
        $game->setTemplateVar('POSSIBLE_BORDER', $possibleBorder);
        $game->setTemplateVar('POSSIBLE_ADMIN_REGION', $possibleAdminRegion);
        $game->setTemplateVar('FIELDS_PER_SECTION', MapEnum::FIELDS_PER_SECTION);
        $game->setTemplateVar('SECTION_ID', $section_id);

        $helper = $this->starmapUiFactory->createMapSectionHelper();
        $helper->setTemplateVars(
            $game,
            $layer,
            $section_id,
            true,
            $this->request->getDirection()
        );

        $game->addExecuteJS(sprintf(
            "registerNavKeys('admin/', '%s', '', true);",
            self::VIEW_IDENTIFIER
        ), GameEnum::JS_EXECUTION_AJAX_UPDATE);
    }
}
