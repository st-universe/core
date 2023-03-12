<?php

declare(strict_types=1);

namespace Stu\Component\Game;


final class ModuleViewEnum
{
    public const MODULE_VIEW_INDEX = 'index';
    public const MODULE_VIEW_MAINDESK = 'maindesk';
    public const MODULE_VIEW_COLONY = 'colony';
    public const MODULE_VIEW_SHIP = 'ship';
    public const MODULE_VIEW_STATION = 'station';
    public const MODULE_VIEW_COMM = 'comm';
    public const MODULE_VIEW_PM = 'pm';
    public const MODULE_VIEW_RESEARCH = 'research';
    public const MODULE_VIEW_TRADE = 'trade';
    public const MODULE_VIEW_ALLIANCE = 'alliance';
    public const MODULE_VIEW_DATABASE = 'database';
    public const MODULE_VIEW_HISTORY = 'history';
    public const MODULE_VIEW_STARMAP = 'starmap';

    public const MODULE_VIEW_ARRAY = [
        self::MODULE_VIEW_INDEX => ['view' => self::MODULE_VIEW_INDEX, 'title' => 'Login-Page'],
        self::MODULE_VIEW_MAINDESK => ['view' => self::MODULE_VIEW_MAINDESK, 'title' => 'Maindesk'],
        self::MODULE_VIEW_COLONY => ['view' => self::MODULE_VIEW_COLONY, 'title' => 'Kolonien'],
        self::MODULE_VIEW_SHIP => ['view' => self::MODULE_VIEW_SHIP, 'title' => 'Schiffe'],
        self::MODULE_VIEW_STATION => ['view' => self::MODULE_VIEW_STATION, 'title' => 'Stationen'],
        self::MODULE_VIEW_COMM => ['view' => self::MODULE_VIEW_COMM, 'title' => 'KommNet'],
        self::MODULE_VIEW_PM => ['view' => self::MODULE_VIEW_PM, 'title' => 'Nachrichten'],
        self::MODULE_VIEW_RESEARCH => ['view' => self::MODULE_VIEW_RESEARCH, 'title' => 'Forschung'],
        self::MODULE_VIEW_TRADE => ['view' => self::MODULE_VIEW_TRADE, 'title' => 'Handel'],
        self::MODULE_VIEW_ALLIANCE => ['view' => self::MODULE_VIEW_ALLIANCE, 'title' => 'Allianz'],
        self::MODULE_VIEW_DATABASE => ['view' => self::MODULE_VIEW_DATABASE, 'title' => 'Datenbank'],
        self::MODULE_VIEW_HISTORY => ['view' => self::MODULE_VIEW_HISTORY, 'title' => 'Ereignisse'],
        self::MODULE_VIEW_STARMAP => ['view' => self::MODULE_VIEW_STARMAP, 'title' => 'Karte'],
    ];
}
