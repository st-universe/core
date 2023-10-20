<?php

declare(strict_types=1);

namespace Stu\Component\Player;

enum AwardTypeEnum: int
{
        // player awards
    case RESEARCHED_STATIONS = 1;
    case BORG_FIGHT = 14;
    case INTERSTELLAR_RESEARCH = 15;
    case SPACECRAFT = 16;
    case IMPORTANT_ROLE = 17;
    case RPG_AQUA = 18;
    case ADVENT = 19;
    case LOTTERY_WINNER = 49;


        //npc awards
    case NPC_FOED_POS = 2;
    case NPC_FOED_NEG = 3;

    case NPC_ROM_POS = 4;
    case NPC_ROM_NEG = 5;

    case NPC_KLING_POS = 6;
    case NPC_KLING_NEG = 7;

    case NPC_CARD_POS = 8;
    case NPC_CARD_NEG = 9;

    case NPC_FERG_POS = 10;
    case NPC_FERG_NEG = 11;

    case NPC_ORI_POS = 12;
    case NPC_ORI_NEG = 13;


        // community awards
    case FINDING_CAPTAIN = 100;
}
