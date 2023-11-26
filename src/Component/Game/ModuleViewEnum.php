<?php

declare(strict_types=1);

namespace Stu\Component\Game;

enum ModuleViewEnum: string
{
    case INDEX = 'index';
    case GAME = 'game';
    case MAINDESK = 'maindesk';
    case COLONY = 'colony';
    case SHIP = 'ship';
    case STATION = 'station';
    case COMMUNICATION = 'comm';
    case PM = 'pm';
    case NOTES = 'notes';
    case RESEARCH = 'research';
    case TRADE = 'trade';
    case ALLIANCE = 'alliance';
    case DATABASE = 'database';
    case HISTORY = 'history';
    case MAP = 'starmap';
    case OPTIONS = 'options';
    case PROFILE = 'userprofile';
    case ADMIN = 'admin';

    public function getPhpPage(): string
    {
        return sprintf('%s.php', $this->value);
    }

    public function getTitle(): string
    {
        return match ($this) {
            self::INDEX => 'Star Trek Universe - Login',
            self::GAME => 'Star Trek Universe',
            self::MAINDESK => 'Maindesk',
            self::COLONY => 'Kolonien',
            self::SHIP => 'Schiffe',
            self::STATION => 'Stationen',
            self::COMMUNICATION => 'KommNet',
            self::PM => 'Nachrichten',
            self::RESEARCH => 'Forschung',
            self::TRADE => 'Handel',
            self::ALLIANCE => 'Allianz',
            self::DATABASE => 'Datenbank',
            self::HISTORY => 'Ereignisse',
            self::MAP => 'Karte',
            self::NOTES => 'Notizen',
            self::OPTIONS => 'Optionen',
            self::PROFILE => 'Spielerprofil',
            self::ADMIN => 'Adminbereich'
        };
    }

    public function getTemplate(): string
    {
        return match ($this) {
            self::INDEX => 'html/index.xhtml',
            self::GAME => 'html/game/game.twig',
            self::MAINDESK => 'html/view/maindesk.twig',
            self::COLONY => 'html/view/colonyList.twig',
            self::SHIP => 'html/view/shipList.twig',
            self::STATION => 'html/view/stationList.twig',
            self::COMMUNICATION => 'html/view/communication.twig',
            self::PM => 'html/view/pmCategory.twig',
            self::RESEARCH => 'html/view/research.twig',
            self::TRADE => 'html/view/trade.twig',
            self::ALLIANCE => 'html/view/alliance.twig',
            self::DATABASE => 'html/view/database.twig',
            self::HISTORY => 'html/view/history.twig',
            self::MAP => 'todo',
            self::NOTES => 'todo',
            self::OPTIONS => 'todo',
            self::PROFILE => 'todo',
            self::ADMIN => 'todo'
        };
    }
}
