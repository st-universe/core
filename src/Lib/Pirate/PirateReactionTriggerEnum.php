<?php

namespace Stu\Lib\Pirate;

enum PirateReactionTriggerEnum: int
{
    case ON_ATTACK = 1;
    case ON_SCAN = 2;
    case ON_INTERCEPTION_BEFORE = 3;
    case ON_INTERCEPTION_AFTER = 4;
    case ON_SUPPORT_CALL = 5;
    case ON_RAGE = 6;
    case ON_TRACTOR = 7;
    case ON_BEAM = 8;

    public function getWrath(): int
    {
        return match ($this) {
            self::ON_ATTACK => 30,
            self::ON_TRACTOR => 25,
            self::ON_BEAM => 15,
            self::ON_INTERCEPTION_BEFORE => 10,
            self::ON_INTERCEPTION_AFTER => 10,
            self::ON_SCAN => 10,
            self::ON_SUPPORT_CALL => 0,
            self::ON_RAGE => 0,
        };
    }

    public function triggerAlternativeReaction(): bool
    {
        return match ($this) {
            self::ON_TRACTOR => false,
            self::ON_ATTACK => true,
            self::ON_INTERCEPTION_BEFORE => false,
            self::ON_INTERCEPTION_AFTER => true,
            self::ON_BEAM => true,
            self::ON_SCAN => true,
            self::ON_SUPPORT_CALL => true,
            self::ON_RAGE => true
        };
    }

    /**
     * @return array<int, int>
     */
    public function getBehaviourProbabilities(): array
    {
        return match ($this) {
            self::ON_ATTACK => [
                PirateBehaviourEnum::RAGE->value => 50,
                PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 25,
                PirateBehaviourEnum::SEARCH_FRIEND->value => 30,
                PirateBehaviourEnum::FLY->value => 20,
                PirateBehaviourEnum::HIDE->value => 20,
                PirateBehaviourEnum::DO_NOTHING->value => 10,
            ],
            self::ON_SCAN => [
                PirateBehaviourEnum::DO_NOTHING->value => 60,
                PirateBehaviourEnum::RAGE->value => 60,
                PirateBehaviourEnum::FLY->value => 20,
                PirateBehaviourEnum::HIDE->value => 20,
                PirateBehaviourEnum::SEARCH_FRIEND->value => 5,
            ],
            self::ON_INTERCEPTION_BEFORE => [
                PirateBehaviourEnum::FLY->value => 50,
                PirateBehaviourEnum::HIDE->value => 25,
                PirateBehaviourEnum::DO_NOTHING->value => 25
            ],
            self::ON_INTERCEPTION_AFTER => [
                PirateBehaviourEnum::RAGE->value => 40,
                PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 15,
                PirateBehaviourEnum::SEARCH_FRIEND->value => 15,
                PirateBehaviourEnum::DO_NOTHING->value => 10,
                PirateBehaviourEnum::FLY->value => 10,
            ],
            self::ON_SUPPORT_CALL => [
                PirateBehaviourEnum::RAGE->value => 100,
                PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 10
            ],
            self::ON_RAGE => [
                PirateBehaviourEnum::RAGE->value => 50,
                PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 20,
                PirateBehaviourEnum::DO_NOTHING->value => 20
            ],
            self::ON_TRACTOR => [
                PirateBehaviourEnum::RAGE->value => 90,
                PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 10

            ],
            self::ON_BEAM => [
                PirateBehaviourEnum::RAGE->value => 50,
                PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 40,
                PirateBehaviourEnum::DO_NOTHING->value => 10
            ]
        };
    }
}
