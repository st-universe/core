<?php

declare(strict_types=1);

namespace Stu\Control;

abstract class ControllerTypeEnum
{

    public const TYPE_PLAYER_SETTING = 'PLAYER_SETTING';
    public const TYPE_HISTORY = 'HISTORY';
    public const TYPE_RESEARCH = 'RESEARCH';
    public const TYPE_TRADE = 'TRADE';
    public const TYPE_COMMUNICATION = 'COMMUNICATION';
    public const TYPE_SHIP = 'SHIP';
    public const TYPE_PLAYER_PROFILE = 'PLAYER_PROFILE';
    public const TYPE_COLONY = 'COLONY';
    public const TYPE_DATABASE = 'DATABASE';
    public const TYPE_INDEX = 'INDEX';
    public const TYPE_STARMAP = 'STARMAP';
    public const TYPE_ALLIANCE = 'ALLIANCE';
    public const TYPE_NOTES = 'NOTES';
    public const TYPE_MAINDESK = 'MAINDESK';
}