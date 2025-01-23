<?php

namespace Stu\Lib\Interaction;

use Stu\Exception\SanityCheckException;

enum InteractionCheckType
{
        // SOURCE
    case EXPECT_SOURCE_SUFFICIENT_CREW;
    case EXPECT_SOURCE_UNSHIELDED;
    case EXPECT_SOURCE_UNCLOAKED;
    case EXPECT_SOURCE_UNWARPED;
    case EXPECT_SOURCE_ENABLED;
    case EXPECT_SOURCE_TACHYON;
    case EXPECT_SOURCE_UNTRACTORED;

        // TARGET
    case EXPECT_TARGET_NO_VACATION;
    case EXPECT_TARGET_NOT_NPC;
    case EXPECT_TARGET_UNSHIELDED;
    case EXPECT_TARGET_UNCLOAKED;
    case EXPECT_TARGET_UNWARPED;
    case EXPECT_TARGET_SAME_USER;
    case EXPECT_TARGET_ALSO_IN_FINISHED_WEB;
    case EXPECT_TARGET_DOCKED_OR_NO_ION_STORM;

    public function getReason(string $placeholder = ''): string
    {
        return match ($this) {
            self::EXPECT_SOURCE_SUFFICIENT_CREW => 'Nicht genügend Crew vorhanden',
            self::EXPECT_SOURCE_UNSHIELDED => 'Die Schilde sind aktiviert',
            self::EXPECT_SOURCE_UNCLOAKED => 'Die Tarnung ist aktiviert',
            self::EXPECT_SOURCE_UNWARPED => 'Der Warpantrieb ist aktiviert',
            self::EXPECT_SOURCE_ENABLED => sprintf('%s ist kampfunfähig', $placeholder),
            self::EXPECT_SOURCE_UNTRACTORED => 'Das Schiff wird von einem Traktorstrahl gehalten',
            self::EXPECT_TARGET_NO_VACATION => 'Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!',
            self::EXPECT_TARGET_NOT_NPC => 'Aktion nicht möglich, der Spieler ist NPC!',
            self::EXPECT_TARGET_UNSHIELDED => 'Das Ziel hat die Schilde aktiviert',
            self::EXPECT_TARGET_UNWARPED => 'Das Ziel hat den Warpantrieb aktiviert',
            self::EXPECT_TARGET_ALSO_IN_FINISHED_WEB => 'Das Ziel ist nicht mit im Energienetz gefangen',
            self::EXPECT_TARGET_DOCKED_OR_NO_ION_STORM => 'Beamen nicht möglich während eines Ionensturms',
            self::EXPECT_SOURCE_TACHYON => throw new SanityCheckException('Tried to interact with cloaked entity without active tachyon'),
            self::EXPECT_TARGET_SAME_USER => throw new SanityCheckException('Tried to interact with entity of other user'),
            self::EXPECT_TARGET_UNCLOAKED => throw new SanityCheckException('Tried to interact with cloaked entity')
        };
    }
}
