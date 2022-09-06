<?php

declare(strict_types=1);

namespace Stu\Component\Player;

final class UserAwardEnum
{
    // award types
    public const RESEARCHED_STATIONS = 1;

    public const NPC_FOED_POS = 2;

    public const NPC_FOED_NEG = 3;

    public const NPC_ROM_POS = 4;

    public const NPC_ROM_NEG = 5;

    public const NPC_KLING_POS = 6;

    public const NPC_KLING_NEG = 7;

    public const NPC_CARD_POS = 8;

    public const NPC_CARD_NEG = 9;

    public const NPC_FERG_POS = 10;

    public const NPC_FERG_NEG = 11;

    public const NPC_ORI_POS = 12;

    public const NPC_ORI_NEG = 13;

    public const BORG_FIGHT = 14;

    public const INTERSTELLAR_RESEARCH = 15;

    public const SPACECRAFT = 16;

    public const IMPORTANT_ROLE = 17;

    public const RPG_AQUA = 18;

    public const ADVENT = 19;

    public const FINDING_CAPTAIN = 100;

    public static function getDescription(int $awardType): string
    {
        switch ($awardType) {
            case UserAwardEnum::RESEARCHED_STATIONS:
                return _("Stationen erforscht");
            case UserAwardEnum::NPC_FOED_POS:
                return _("Der Spieler hat sich in den Augen der Föderation lobend hervorgetan");
            case UserAwardEnum::NPC_FOED_NEG:
                return _("Der Spieler hat sich gegen die Föderation versündigt");
            case UserAwardEnum::NPC_ROM_POS:
                return _("Der Spieler hat dem Romulanischen Imperium treue Dienste geleistet");
            case UserAwardEnum::NPC_ROM_NEG:
                return _("Der Spieler hat sich gegen das Romulanische Imperium versündigt");
            case UserAwardEnum::NPC_KLING_POS:
                return _("Der Spieler hat im Auge des Klingonischen Imperiums ehrenvolles geleistet");
            case UserAwardEnum::NPC_KLING_NEG:
                return _("Der Spieler hat sich gegen das Klingonische Imperium versündigt");
            case UserAwardEnum::NPC_CARD_POS:
                return _("Der Spieler hat seine Loyalität zur Cardassianischen Union bewiesen");
            case UserAwardEnum::NPC_CARD_NEG:
                return _("Der Spieler hat sich gegen die Cardassianische Union versündigt");
            case UserAwardEnum::NPC_FERG_POS:
                return _("Der Spieler hat der Ferengi Allianz wertvolle Dienste erwiesen");
            case UserAwardEnum::NPC_FERG_NEG:
                return _("Der Spieler hat sich gegen die Ferengi Allianz versündigt");
            case UserAwardEnum::NPC_ORI_POS:
                return _("Der Spieler hat alles daran gesetzt Recht und Ordnung nachhaltig zu unterwandern");
            case UserAwardEnum::NPC_ORI_NEG:
                return _("Der Spieler hat sich gegen das organisierte Verbrechen versündigt");
            case UserAwardEnum::BORG_FIGHT:
                return _("Der Spieler hat sich den Borg im Kampf gestellt");
            case UserAwardEnum::INTERSTELLAR_RESEARCH:
                return _("Der Spieler hat sämtliche Sternensysteme kartographiert");
            case UserAwardEnum::SPACECRAFT:
                return _("Der Spieler hat die Raumfahrt gemeistert");
            case UserAwardEnum::IMPORTANT_ROLE:
                return _("Der Spieler hat eine besondere Rolle in der Ausdehnung eingenommen");
            case UserAwardEnum::RPG_AQUA:
                return _("Der Spieler hat an der Erforschung einer aquatischen Welt teilgenommen");
            case UserAwardEnum::ADVENT:
                return _("Der Spieler hat an dem Weihnachtskalender teilgenommen");
            case UserAwardEnum::FINDING_CAPTAIN:
                return _("Der Spieler hatte im Jahr 2392 Probleme den ranghöchsten Offizier zu finden");
        }
        return '';
    }
}
