<?php

declare(strict_types=1);

namespace Stu\Component\Game;

use RuntimeException;
use Stu\Lib\Component\ComponentEnumInterface;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Game\Component\GameComponentEnum;

enum ModuleEnum: string
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
    case STARMAP = 'starmap';
    case OPTIONS = 'options';
    case USERPROFILE = 'userprofile';
    case ADMIN = 'admin';
    case NPC = 'npc';

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
            self::STARMAP => 'Karte',
            self::NOTES => 'Notizen',
            self::OPTIONS => 'Optionen',
            self::USERPROFILE => 'Spielerprofil',
            self::ADMIN => 'Adminbereich',
            self::NPC => 'NPC'
        };
    }

    public function getTemplate(): string
    {
        return match ($this) {
            self::INDEX => 'html/index/index.twig',
            self::GAME => 'html/game/game.twig',
            self::MAINDESK => 'html/view/maindesk.twig',
            self::COLONY => 'html/view/colonylist.twig',
            self::SHIP => 'html/view/shiplist.twig',
            self::STATION => 'html/view/stationList.twig',
            self::COMMUNICATION => 'html/view/communication.twig',
            self::PM => 'html/view/pmCategory.twig',
            self::RESEARCH => 'html/view/research.twig',
            self::TRADE => 'html/view/trade.twig',
            self::ALLIANCE => 'html/view/alliance.twig',
            self::DATABASE => 'html/view/database.twig',
            self::HISTORY => 'html/view/history.twig',
            self::STARMAP => 'html/view/map.twig',
            self::NOTES => 'not needed',
            self::OPTIONS => 'html/view/options.twig',
            self::USERPROFILE => 'html/view/userprofile.twig',
            self::ADMIN => 'not needed',
            self::NPC => 'not needed'
        };
    }

    public function getComponentEnum(string $value): ComponentEnumInterface
    {
        $result =  match ($this) {
            self::GAME => GameComponentEnum::tryFrom($value),
            self::COLONY => ColonyComponentEnum::tryFrom($value),
            default => throw new RuntimeException('no components in this module view')
        };

        return $result ?? GameComponentEnum::OUTDATED;
    }

    public function getCommonModule(): ?string
    {
        return match ($this) {
            self::SHIP,
            self::STATION => 'SPACECRAFT',
            default => null
        };
    }
}
