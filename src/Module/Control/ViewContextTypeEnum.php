<?php

namespace Stu\Module\Control;

enum ViewContextTypeEnum: int
{
    case VIEW = 1;
    case HOST = 2;
    case COLONY_MENU = 3;
    case MODULE = 4;
    case KN_POST = 5;
    case TACHYON_SCAN_JUST_HAPPENED = 6;
    case FILTER_ACTIVE = 7;
    case NO_AJAX = 8;
}
