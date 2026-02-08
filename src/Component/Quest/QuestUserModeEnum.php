<?php

declare(strict_types=1);

namespace Stu\Component\Quest;

enum QuestUserModeEnum: int
{
    case ACTIVE_MEMBER = 1;
    case APPLICANT = 2;
    case INVITED = 3;
    case REJECTED_EXCLUDED = 4;

    public function getName(): string
    {
        return match ($this) {
            self::ACTIVE_MEMBER => 'Aktive Mitglieder',
            self::APPLICANT => 'Bewerber',
            self::INVITED => 'Eingeladen',
            self::REJECTED_EXCLUDED => 'Abgelehnt/Ausgeschlossen'
        };
    }

    public function getCssClass(): string
    {
        return match ($this) {
            self::ACTIVE_MEMBER => 'pos',
            self::APPLICANT => 'neg',
            self::INVITED => 'positive',
            self::REJECTED_EXCLUDED => 'negative'
        };
    }
}
