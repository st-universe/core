<?php

declare(strict_types=1);

namespace Stu\Component\Player;

final class UserAwardEnum
{
    // player awards
    public const int RESEARCHED_STATIONS = 1;
    public const int BORG_FIGHT = 14;
    public const int INTERSTELLAR_RESEARCH = 15;
    public const int SPACECRAFT = 16;
    public const int IMPORTANT_ROLE = 17;
    public const int RPG_AQUA = 18;
    public const int ADVENT = 19;
    public const int LOTTERY_WINNER = 49;


    //npc awards
    public const int NPC_FOED_POS = 2;
    public const int NPC_FOED_NEG = 3;

    public const int NPC_ROM_POS = 4;
    public const int NPC_ROM_NEG = 5;

    public const int NPC_KLING_POS = 6;
    public const int NPC_KLING_NEG = 7;

    public const int NPC_CARD_POS = 8;
    public const int NPC_CARD_NEG = 9;

    public const int NPC_FERG_POS = 10;
    public const int NPC_FERG_NEG = 11;

    public const int NPC_ORI_POS = 12;
    public const int NPC_ORI_NEG = 13;


    // community awards
    public const int FINDING_CAPTAIN = 100;
}
