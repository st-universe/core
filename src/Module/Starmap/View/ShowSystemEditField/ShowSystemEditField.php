<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowSystemEditField;

use MapFieldType;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use SystemMap;

final class ShowSystemEditField implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SYSTEM_EDITFIELD';

    private $showSystemEditFieldRequest;

    public function __construct(
        ShowSystemEditFieldRequestInterface $showSystemEditFieldRequest
    ) {
        $this->showSystemEditFieldRequest = $showSystemEditFieldRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $possibleFieldTypes = ['row_0', 'row_1', 'row_2', 'row_3', 'row_4', 'row_5'];
        foreach (MapFieldType::getList(' WHERE region_id=0') as $key => $value) {
            if ($value->getIsSystem()) {
                continue;
            }
            $possibleFieldTypes['row_' . ($key % 6)][] = $value;
        }

        $field = new SystemMap($this->showSystemEditFieldRequest->getFieldId());

        $game->setPageTitle(_('Feld wählen'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/macros.xhtml/mapeditor_system_fieldselector');
        $game->setTemplateVar('POSSIBLE_FIELD_TYPES', $possibleFieldTypes);
        $game->setTemplateVar('SELECTED_MAP_FIELD', $field);
    }
}