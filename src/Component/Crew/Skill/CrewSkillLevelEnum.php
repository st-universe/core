<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

enum CrewSkillLevelEnum: string
{
    case ADMIRAL = 'ADMIRAL';
    case COMMODORE = 'COMMODORE';
    case SENIOR_COMMANDER = 'SENIOR_COMMANDER';
    case COMMANDER = 'COMMANDER';
    case LIEUTENANT_COMMANDER = 'LIEUTENANT_COMMANDER';
    case LIEUTENANT = 'LIEUTENANT';
    case JUNIOR_LIEUTENANT = 'JUNIOR_LIEUTENANT';
    case ENSIGN = 'ENSIGN';
    case CADET = 'CADET';
    case RECRUIT = 'RECRUIT';

    public function getNeededExpertise(): int
    {
        return match ($this) {
            self::ADMIRAL => 100_000,
            self::COMMODORE => 50_000,
            self::SENIOR_COMMANDER => 20_000,
            self::COMMANDER => 10_000,
            self::LIEUTENANT_COMMANDER => 5_000,
            self::LIEUTENANT => 2_000,
            self::JUNIOR_LIEUTENANT => 1_000,
            self::ENSIGN => 300,
            self::CADET => 100,
            self::RECRUIT => 0
        };
    }

    public function getBonusPercentage(): int
    {
        return match ($this) {
            self::ADMIRAL => 25,
            self::COMMODORE => 20,
            self::SENIOR_COMMANDER => 15,
            self::COMMANDER => 12,
            self::LIEUTENANT_COMMANDER => 9,
            self::LIEUTENANT => 6,
            self::JUNIOR_LIEUTENANT => 4,
            self::ENSIGN => 2,
            self::CADET => 1,
            self::RECRUIT => 0
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ADMIRAL => 'Admiral',
            self::COMMODORE => 'Kommodore',
            self::SENIOR_COMMANDER => 'Oberkommandant',
            self::COMMANDER => 'Kommandant',
            self::LIEUTENANT_COMMANDER => 'Oberleutnant',
            self::LIEUTENANT => 'Leutnant',
            self::JUNIOR_LIEUTENANT => 'Unterleutnant',
            self::ENSIGN => 'Fähnrich',
            self::CADET => 'Kadett',
            self::RECRUIT => 'Rekrut'
        };
    }

    public static function getForExpertise(int $expertise): CrewSkillLevelEnum
    {
        return match (true) {
            $expertise < self::CADET->getNeededExpertise() => self::RECRUIT,
            $expertise < self::ENSIGN->getNeededExpertise() => self::CADET,
            $expertise < self::JUNIOR_LIEUTENANT->getNeededExpertise() => self::ENSIGN,
            $expertise < self::LIEUTENANT->getNeededExpertise() => self::JUNIOR_LIEUTENANT,
            $expertise < self::LIEUTENANT_COMMANDER->getNeededExpertise() => self::LIEUTENANT,
            $expertise < self::COMMANDER->getNeededExpertise() => self::LIEUTENANT_COMMANDER,
            $expertise < self::SENIOR_COMMANDER->getNeededExpertise() => self::COMMANDER,
            $expertise < self::COMMODORE->getNeededExpertise() => self::SENIOR_COMMANDER,
            $expertise < self::ADMIRAL->getNeededExpertise() => self::COMMODORE,
            default => self::ADMIRAL
        };
    }
}
