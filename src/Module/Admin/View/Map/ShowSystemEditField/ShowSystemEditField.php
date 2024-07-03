<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\ShowSystemEditField;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class ShowSystemEditField implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SYSTEM_EDITFIELD';

    public function __construct(private ShowSystemEditFieldRequestInterface $showSystemEditFieldRequest, private MapFieldTypeRepositoryInterface $mapFieldTypeRepository, private StarSystemMapRepositoryInterface $starSystemMapRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $possibleFieldTypes = ['row_0' => [], 'row_1' => [], 'row_2' => [], 'row_3' => [], 'row_4' => [], 'row_5' => []];
        foreach ($this->mapFieldTypeRepository->findAll() as $key => $value) {
            if ($value->getIsSystem()) {
                continue;
            }
            $possibleFieldTypes['row_' . ($key % 6)][] = $value;
        }

        $field = $this->starSystemMapRepository->find($this->showSystemEditFieldRequest->getFieldId());

        $game->setPageTitle(_('Feld wählen'));
        $game->setMacroAndTemplate('html/admin/mapeditor_macros.xhtml/mapeditor_system_fieldselector', 'html/admin/ajaxwindow.xhtml');
        $game->setTemplateVar('POSSIBLE_FIELD_TYPES', $possibleFieldTypes);
        $game->setTemplateVar('SELECTED_MAP_FIELD', $field);
    }
}
