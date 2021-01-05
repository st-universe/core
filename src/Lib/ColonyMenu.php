<?php

declare(strict_types=1);

use Stu\Component\Colony\ColonyEnum;

class ColonyMenu
{

    private $selectedColonyMenu;

    function __construct($selectedColonyMenu)
    {
        $this->selectedColonyMenu = $selectedColonyMenu;
    }

    private function getMenuType()
    {
        return (int)request::getInt('menu');
    }

    public function __get($value)
    {
        if ($this->selectedColonyMenu == $value) {
            return 'selected';
        }
        if ($this->getMenuType() == $value) {
            return "selected";
        }
        if ($value == ColonyEnum::MENU_INFO && $this->getMenuType() == 0 && $this->selectedColonyMenu === null) {
            return 'selected';
        }
        return false;
    }

}
