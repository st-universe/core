<?php

namespace Stu\Module\Control;

enum ViewContextTypeEnum: int
{
    case VIEW = 1;
    case MODULE_VIEW = 2;
    case HOST = 3;
    case COLONY_MENU = 4;
    case MODULE = 5;
    case KN_POST = 6;
    case TACHYON_SCAN_JUST_HAPPENED = 7;
    case FILTER_ACTIVE = 8;
    case NO_AJAX = 9;
}
