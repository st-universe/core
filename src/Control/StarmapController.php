<?php

namespace Stu\Control;

use Bordertypes;
use MapField;
use MapFieldType;
use request;
use StarSystem;
use StarSystemData;
use Stu\Lib\Session;
use SystemMap;
use Tuple;
use UserYRow;
use YRow;

final class StarmapController extends GameController
{

    const FIELDS_PER_SECTION = 20;

    private $default_tpl = "html/starmap.xhtml";

    function __construct(
        Session $session
    )
    {
        parent::__construct($session, $this->default_tpl, "/ Sternenkarte");
        $this->addNavigationPart(new Tuple("starmap.php", "Sternenkarte"));

        $this->addCallback('B_EDIT_FIELD', 'editField');
        $this->addCallback('B_EDIT_SYSTEM_FIELD', 'editSystemField');
        $this->addCallback('B_CREATE_STARSYSTEM', 'createStarSystem');
        $this->addCallback('B_EDIT_BORDER', 'editBorder');

        $this->addView("SHOW_EDITOR", "showEditor");
        $this->addView("EDIT_SECTION", "editSection");
        $this->addView("SHOW_SECTION", "showSection");
        $this->addView("SHOW_OVERALL", "showOverallMap");
        $this->addView("SHOW_EDITFIELD", "showEditField");
        $this->addView("SHOW_SYSTEM_EDITFIELD", "showSystemEditField");
        $this->addView("SHOW_SYSTEM", "showSystem");
        $this->addView('SHOW_STARMAP_POSITION', 'showStarmapByPosition');
    }

    private function setEditMode()
    {
        $this->navigationAction = 'EDIT_SECTION';
    }

    private function isEditMode()
    {
        return $this->navigationAction == 'EDIT_SECTION';
    }

    protected function showStarmapByPosition()
    {
        $cx = request::getIntFatal('x');
        $cy = request::getIntFatal('y');

        if ($cx < 1) {

        }
        if ($cx < 1 || $cx > MAP_MAX_X || $cy < 1 || $cy > MAP_MAX_Y) {
            return;
        }
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/macros.xhtml/starmap');
    }

    function showEditor()
    {
        $this->setEditMode();
        $this->setTemplateFile('html/mapeditor_overview.xhtml');
        $this->addNavigationPart(new Tuple("starmap.php?SHOW_EDITOR=1", "Editor"));
        $this->setPageTitle("Mapeditor");
    }

    function editSection()
    {
        $this->setEditMode();
        $this->setTemplateFile('html/mapeditor_section.xhtml');
        $this->addNavigationPart(new Tuple("starmap.php?SHOW_EDITOR=1", "Editor"));
        $this->addNavigationPart(new Tuple("starmap.php?EDIT_SECTION=1&x=" . $this->getRequestX() . "&y=" . $this->getRequestY(),
            "Sektion editieren"));
        $this->setPageTitle("Sektion editieren");
    }

    protected function showSection()
    {
        $this->setTemplateFile('html/starmap_section.xhtml');
        $this->addNavigationPart(new Tuple("starmap.php?SHOW_SECTION=1&x=" . $this->getRequestX() . "&y=" . $this->getRequestY(),
            "Sektion anzeigen"));
        $this->setPageTitle("Sektion anzeigen");
    }

    function showSystem()
    {
        $this->setEditMode();
        $sysid = request::getIntFatal('sysid');
        $this->loadSystem($sysid);
        $this->setTemplateFile('html/mapeditor_system.xhtml');
        $this->addNavigationPart(new Tuple("starmap.php?SHOW_SYSTEM=1&sysid=" . $sysid, "System editieren"));
        $this->setPageTitle("System editieren");
    }

    function showEditField()
    {
        $this->setEditMode();
        $this->setPageTitle("Feld wählen");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/macros.xhtml/mapeditor_fieldselector');
    }

    function showSystemEditField()
    {
        $this->setEditMode();
        $this->setPageTitle("Feld wählen");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/macros.xhtml/mapeditor_system_fieldselector');
    }

    public function getXHeadRow()
    {
        $ret = array();
        for ($j = 1; $j <= MAP_MAX_X / MAPFIELDS_PER_SECTION; $j++) {
            $ret[] = $j;
        }
        return $ret;
    }

    private $fieldtypes = null;

    function getPossibleFieldTypes()
    {
        if ($this->fieldtypes === null) {
            $num = 0;
            $ret = array('row_0', 'row_1', 'row_2', 'row_3', 'row_4', 'row_5');
            $fields = MapFieldType::getList(' WHERE region_id=0');
            foreach ($fields as $key => $value) {
                if ($value->getIsSystem()) {
                    continue;
                }
                $ret['row_' . $num][] = $value;
                $num++;
                if ($num == 6) {
                    $num = 0;
                }
            }
            $this->fieldtypes = $ret;
        }
        return $this->fieldtypes;
    }

    private $mapsection = null;

    function getMapSections()
    {
        if ($this->mapsection === null) {
            $this->mapsection = $this->loadMapSections();
        }
        return $this->mapsection;
    }

    function loadMapSections()
    {
        $ret = array();
        $k = 1;
        for ($i = 1; $i <= MAP_MAX_Y / self::FIELDS_PER_SECTION; $i++) {
            for ($j = 1; $j <= MAP_MAX_X / self::FIELDS_PER_SECTION; $j++) {
                $ret[$i][$j] = $k;
                $k++;
            }
        }
        return $ret;
    }

    function getEditorCols()
    {
        return MAP_MAX_X / self::FIELDS_PER_SECTION;
    }

    private $mapdata = null;

    function getMapData()
    {
        if ($this->mapdata === null) {
            $this->mapdata = $this->fillMapData();
        }
        return $this->mapdata;
    }

    function fillMapData()
    {
        $range = range($this->getMinY(), $this->getMaxY());
        $ret = array();
        foreach ($range as $key => $value) {
            if (!$this->isEditMode()) {
                $ret[] = new UserYRow($value, $this->getMinX(), $this->getMaxX());
            } else {
                $ret[] = new YRow($value, $this->getMinX(), $this->getMaxX());
            }
        }
        return $ret;
    }

    function getSection()
    {
        $x = $this->getRequestX();
        $y = $this->getRequestY();

        if ($x < 1) {
            $x = 1;
        }
        if ($y < 1) {
            $y = 1;
        }
        $this->maxx = $x * self::FIELDS_PER_SECTION;
        $this->minx = $this->maxx - self::FIELDS_PER_SECTION + 1;

        $this->maxy = $y * self::FIELDS_PER_SECTION;
        $this->miny = $this->maxy - self::FIELDS_PER_SECTION + 1;
    }

    function loadSystem($systemId)
    {
        $sys = new StarSystem($systemId);
        $this->minx = 1;
        $this->miny = 1;

        $this->maxx = SystemMap::getMaxX($systemId);
        $this->maxy = SystemMap::getMaxY($systemId);

        $this->mapdata = $this->fillSystemMapData($sys->getId());
    }

    function fillSystemMapData($systemId)
    {
        $ret = array();
        for ($i = $this->getMinY(); $i <= $this->getMaxY(); $i++) {
            if (!$this->isEditMode()) {
                $ret[] = new UserYRow($i, $this->getMinX(), $this->getMaxX(), $systemId);
            } else {
                $ret[] = new YRow($i, $this->getMinX(), $this->getMaxX(), $systemId);
            }
        }
        return $ret;
    }

    function getLeftPreviewRow()
    {
        $x = $this->getRequestX();
        $y = $this->getRequestY();
        if ($x - 1 < 1) {
            return false;
        }

        $maxx = $x * self::FIELDS_PER_SECTION;
        $minx = $maxx - self::FIELDS_PER_SECTION + 1;
        $maxy = $y * self::FIELDS_PER_SECTION;
        $miny = $maxy - self::FIELDS_PER_SECTION + 1;
        $row = array();
        for ($i = $miny; $i <= $maxy; $i++) {
            if (!$this->isEditMode()) {
                $row[] = new UserYRow($i, $minx - 1, $minx - 1);
            } else {
                $row[] = new YRow($i, $minx - 1, $minx - 1);
            }
        }

        return $row;
    }

    private $rx = null;

    /**
     */
    public function getRequestX()
    { #{{{
        if ($this->rx === null) {
            $x = request::indInt('x');
            if ($x < 1) {
                $x = 1;
            }
            if ($x * FIELDS_PER_SECTION > MAP_MAX_X * FIELDS_PER_SECTION) {
                $x = FIELDS_PER_SECTION / MAP_MAX_X;
            }
            $this->rx = $x;
        }
        return $this->rx;
    } # }}}

    private $ry = null;

    /**
     */
    public function getRequestY()
    { #{{{
        if ($this->ry === null) {
            $y = request::indInt('y');
            if ($y < 1) {
                $y = 1;
            }
            if ($y * FIELDS_PER_SECTION > MAP_MAX_Y * FIELDS_PER_SECTION) {
                $y = FIELDS_PER_SECTION / MAP_MAX_Y;
            }
            $this->ry = $y;
        }
        return $this->ry;
    } # }}}


    function getRightPreviewRow()
    {
        $x = $this->getRequestX();
        $y = $this->getRequestY();
        if ($x * self::FIELDS_PER_SECTION + 1 > MAP_MAX_X) {
            return false;
        }

        $maxx = $x * self::FIELDS_PER_SECTION;
        $minx = $maxx - self::FIELDS_PER_SECTION + 1;
        $maxy = $y * self::FIELDS_PER_SECTION;
        $miny = $maxy - self::FIELDS_PER_SECTION + 1;
        $row = array();
        for ($i = $miny; $i <= $maxy; $i++) {
            if (!$this->isEditMode()) {
                $row[] = new UserYRow($i, $maxx + 1, $maxx + 1);
            } else {
                $row[] = new YRow($i, $maxx + 1, $maxx + 1);
            }
        }

        return $row;
    }

    function getTopPreviewRow()
    {
        $x = $this->getRequestX();
        $y = $this->getRequestY();
        if ($y - 1 < 1) {
            return false;
        }

        $maxx = $x * self::FIELDS_PER_SECTION;
        $minx = $maxx - self::FIELDS_PER_SECTION + 1;

        if (!$this->isEditMode()) {
            $row = new UserYRow($y * self::FIELDS_PER_SECTION - self::FIELDS_PER_SECTION, $minx, $maxx);
        } else {
            $row = new YRow($y * self::FIELDS_PER_SECTION - self::FIELDS_PER_SECTION, $minx, $maxx);
        }
        return $row->getFields();
    }

    function getBottomPreviewRow()
    {
        $x = $this->getRequestX();
        $y = $this->getRequestY();
        if ($y * self::FIELDS_PER_SECTION + 1 > MAP_MAX_Y) {
            return false;
        }

        $maxx = $x * self::FIELDS_PER_SECTION;
        $minx = $maxx - self::FIELDS_PER_SECTION + 1;
        if (!$this->isEditMode()) {
            $row = new UserYRow($y * self::FIELDS_PER_SECTION + 1, $minx, $maxx);
        } else {
            $row = new YRow($y * self::FIELDS_PER_SECTION + 1, $minx, $maxx);
        }
        return $row->getFields();
    }

    private $minx = null;
    private $maxx = null;
    private $miny = null;
    private $maxy = null;

    function getMinX()
    {
        if ($this->minx === null) {
            $this->getSection();
        }
        return $this->minx;
    }

    function getMaxX()
    {
        if ($this->maxx === null) {
            $this->getSection();
        }
        return $this->maxx;
    }

    function getMinY()
    {
        if ($this->miny === null) {
            $this->getSection();
        }
        return $this->miny;
    }

    function getMaxY()
    {
        if ($this->maxy === null) {
            $this->getSection();
        }
        return $this->maxy;
    }

    function getHeadRow()
    {
        return range($this->getMinX(), $this->getMaxX());
    }

    function showOverallMap()
    {
        $types = array();
        $img = imagecreatetruecolor(MAP_MAX_X * 15, MAP_MAX_Y * 15);

        // mapfields
        $gna = 1;
        $cury = 0;
        $curx = 0;

        $map = MapField::getFieldsBy('cx BETWEEN 1 AND ' . MAP_MAX_X . ' AND cy BETWEEN 1 AND ' . MAP_MAX_Y);
        while ($data = mysqli_fetch_assoc($map)) {
            if ($gna != $data['cy']) {
                $gna = $data['cy'];
                $curx = 0;
                $cury += 15;
            }
            if ($data['bordertype_id'] > 0) {
                $border = imagecreatetruecolor(15, 15);
                $var = '#' . getBorderType($data['bordertype_id'])->getColor();
                $arr = sscanf($var, '#%2x%2x%2x');
                $col = imagecolorallocate($border, $arr[0], $arr[1], $arr[2]);
                imagefill($border, 0, 0, $col);
                imagecopy($img, $border, $curx, $cury, 0, 0, 15, 15);
                $curx += 15;
                continue;
            }
            if (!array_key_exists($data['field_id'], $types)) {
                $maptype = new MapFieldType($data['field_id']);
                $types[$data['field_id']] = imagecreatefromgif(APP_PATH . '/gfx/map/' . $maptype->getType() . '.gif');
            }
            imagecopyresized($img, $types[$data['field_id']], $curx, $cury, 0, 0, 15, 15, 30, 30);
            $curx += 15;
        }
        header("Content-type: image/png");
        imagepng($img);
        imagedestroy($img);
        exit;
    }

    private $selectedfield = null;

    function getSelectedField()
    {
        if ($this->selectedfield === null) {
            $this->selectedfield = new MapField(request::getIntFatal('field'));
        }
        return $this->selectedfield;
    }

    function getSelectedSystemField()
    {
        if ($this->selectedfield === null) {
            $this->selectedfield = new SystemMap(request::getIntFatal('field'));
        }
        return $this->selectedfield;
    }

    function editField()
    {
        $typeid = request::getIntFatal('type');
        $type = new MapFieldType($typeid);
        $this->getSelectedField()->setFieldId($type->getId());
        $this->getSelectedField()->save();
    }

    function editSystemField()
    {
        $typeid = request::getIntFatal('type');
        $type = new MapFieldType($typeid);
        $this->getSelectedSystemField()->setFieldId($type->getId());
        $this->getSelectedSystemField()->save();
    }

    function editBorder()
    {
        $typeid = request::getIntFatal('type');
        if ($typeid != 0) {
            $type = new Bordertypes($typeid);
            $this->getSelectedField()->setBordertype($type->getId());
        } else {
            $this->getSelectedField()->setBordertype(0);
        }
        $this->getSelectedField()->save();
    }

    function createStarSystem()
    {
        $width = request::postIntFatal('width');
        $height = request::postIntFatal('height');
        if ($width > 40 || $height > 40) {
            $this->addInformation("Ein System darf maximal 40x40 Felder haben");
            return;
        }
        $name = strip_tags(request::postString('sysname'));
        if (strlen($name) < 5) {
            $this->addInformation("Der Systemname muss aus mindestens 5 Zeichen bestehen");
            return;
        }
        $type = request::postIntFatal('systype');
        if ($type == 0) {
            $this->addInformation("Es wurde kein Typ ausgewählt");
            return;
        }
        $system = new StarSystemData();
        $system->setName($name);
        $system->setType($type);
        $system->save();

        for ($i = 1; $i <= $height; $i++) {
            for ($j = 1; $j <= $width; $j++) {
                SystemMap::addField($system->getId(), $j, $i);
            }
        }

        $this->addInformation("Das Sternensystem wurden erstellt");
    }

    function getPossibleSystems()
    {
        return MapFieldType::getSystemList();
    }

    function getSystemList()
    {
        return StarSystem::getList();
    }

    function getPossibleBordertypes()
    {
        return Bordertypes::getList();
    }

    private $navigationAction = 'SHOW_SECTION';

    public function getNavigationAction()
    {
        return $this->navigationAction;
    }

    function getNavigationHrefUp()
    {
        $sec = request::getInt('sec') - 25;
        return '?' . $this->getNavigationAction() . '=1&x=' . $this->getRequestX() . '&y=' . ($this->getRequestY() > 1 ? $this->getRequestY() - 1 : 1) . '&sec=' . $sec;
    }

    function getNavigationHrefDown()
    {
        $sec = request::getInt('sec') + 25;
        return '?' . $this->getNavigationAction() . '=1&x=' . $this->getRequestX() . '&y=' . ($this->getRequestY() + 1 > MAP_MAX_Y / self::FIELDS_PER_SECTION ? $this->getRequestY() : $this->getRequestY() + 1) . '&sec=' . $sec;
    }

    function getNavigationHrefLeft()
    {
        $sec = request::getInt('sec') - 1;
        return '?' . $this->getNavigationAction() . '=1&x=' . ($this->getRequestX() > 1 ? $this->getRequestX() - 1 : 1) . '&y=' . $this->getRequestY() . '&sec=' . $sec;
    }

    function getNavigationHrefRight()
    {
        $sec = request::getInt('sec') + 1;
        return '?' . $this->getNavigationAction() . '=1&x=' . ($this->getRequestX() + 1 > MAP_MAX_X / self::FIELDS_PER_SECTION ? $this->getRequestX() : $this->getRequestX() + 1) . '&y=' . $this->getRequestX() . '&sec=' . $sec;
    }

    function getNavigationSection()
    {
        return request::getInt('sec');
    }

    public function hasNavigationLeft()
    {
        return request::getInt('x') > 1;
    }

    public function hasNavigationRight()
    {
        return request::getInt('x') * MAPFIELDS_PER_SECTION < MAP_MAX_X;
    }

    public function hasNavigationUp()
    {
        return request::getInt('y') > 1;
    }

    public function hasNavigationBottom()
    {
        return request::getInt('y') * MAPFIELDS_PER_SECTION < MAP_MAX_Y;
    }

    public function getNavigationUp()
    {
        return array(
            "x" => request::getInt('x'),
            "y" => request::getInt('y') - 1
        );
    }

    public function getNavigationDown()
    {
        return array(
            "x" => request::getInt('x'),
            "y" => request::getInt('y') + 1
        );
    }

    public function getNavigationLeft()
    {
        return array(
            "x" => request::getInt('x') - 1,
            "y" => request::getInt('y')
        );
    }

    public function getNavigationRight()
    {
        return array(
            "x" => request::getInt('x') + 1,
            "y" => request::getInt('y')
        );
    }
}
