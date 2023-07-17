<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use request;
use Stu\Component\Colony\ColonyEnum;

class ColonyMenu
{
    private ?int $selectedColonyMenu;

    public function __construct(?int $selectedColonyMenu)
    {
        $this->selectedColonyMenu = $selectedColonyMenu;
    }

    /**
     * @param null|int $value
     *
     * @return false|string
     */
    public function __get($value)
    {
        $menuType = request::getInt('menu');
        if ($this->selectedColonyMenu === $value) {
            return 'selected';
        }

        if ($menuType === $value) {
            return 'selected';
        }

        if ($value === ColonyEnum::MENU_INFO && $menuType === 0 && $this->selectedColonyMenu === null) {
            return 'selected';
        }

        return false;
    }
}
