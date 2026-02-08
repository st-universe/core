<?php

namespace Stu\Lib\Pirate;

enum PirateBehaviourEnum: int
{
    // passive
    case DO_NOTHING = 0;
    case FLY = 1;
    case HIDE = 2;
    case SEARCH_FRIEND = 3;

    // aggressive
    case RUB_COLONY = 4;
    case ATTACK_SHIP = 5;
    case RAGE = 6;
    case CALL_FOR_SUPPORT = 7;
    case ASSAULT_PHALANX = 8;

    // auxiliary
    case GO_ALERT_RED = 9;
    case DEACTIVATE_SHIELDS = 10;

    public function getProbability(): int
    {
        return match ($this) {
            self::FLY => 40,
            self::DO_NOTHING => 30,
            self::HIDE => 20,
            self::RUB_COLONY => 5,
            self::ATTACK_SHIP => 5,
            self::RAGE => 2,
            self::CALL_FOR_SUPPORT => 1,
            self::ASSAULT_PHALANX => 1,
            self::GO_ALERT_RED => 0,
            self::SEARCH_FRIEND => 0,
            self::DEACTIVATE_SHIELDS => 0
        };
    }

    public function needsWeapons(): bool
    {
        return match ($this) {
            self::ATTACK_SHIP,
            self::RAGE => true,
            default => false
        };
    }

    /** @return array<int, int> */
    public static function getBehaviourProbabilities(): array
    {
        return array_map(fn (PirateBehaviourEnum $behaviour): int => $behaviour->getProbability(), self::cases());
    }
}
