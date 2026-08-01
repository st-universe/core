<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Stu\Component\Faction\FactionEnum;

enum CrewSkillLevelEnum: string
{
    case ADMIRAL = 'ADMIRAL';
    case COMMODORE = 'COMMODORE';
    case CAPTAIN = 'CAPTAIN';
    case COMMANDER = 'COMMANDER';
    case LIEUTENANT_COMMANDER = 'LIEUTENANT_COMMANDER';
    case LIEUTENANT = 'LIEUTENANT';
    case JUNIOR_LIEUTENANT = 'JUNIOR_LIEUTENANT';
    case ENSIGN = 'ENSIGN';
    case CREWMAN = 'CREWMAN';
    case CADET = 'CADET';

    public function getNeededExpertise(): int
    {
        return match ($this) {
            self::ADMIRAL => 100_000,
            self::COMMODORE => 50_000,
            self::CAPTAIN => 20_000,
            self::COMMANDER => 10_000,
            self::LIEUTENANT_COMMANDER => 5_000,
            self::LIEUTENANT => 2_000,
            self::JUNIOR_LIEUTENANT => 1_000,
            self::ENSIGN => 300,
            self::CREWMAN => 100,
            self::CADET => 0
        };
    }

    public function getBonusPercentage(): int
    {
        return match ($this) {
            self::ADMIRAL => 25,
            self::COMMODORE => 20,
            self::CAPTAIN => 15,
            self::COMMANDER => 12,
            self::LIEUTENANT_COMMANDER => 9,
            self::LIEUTENANT => 6,
            self::JUNIOR_LIEUTENANT => 4,
            self::ENSIGN => 2,
            self::CREWMAN => 1,
            self::CADET => 0
        };
    }

    public function getAutomaticPromotionTarget(): CrewSkillLevelEnum
    {
        return $this->getNeededExpertise() <= self::LIEUTENANT_COMMANDER->getNeededExpertise()
            ? $this
            : self::LIEUTENANT_COMMANDER;
    }

    public function getNextRank(): ?CrewSkillLevelEnum
    {
        return match ($this) {
            self::LIEUTENANT_COMMANDER => self::COMMANDER,
            self::COMMANDER => self::CAPTAIN,
            self::CAPTAIN => self::COMMODORE,
            self::COMMODORE => self::ADMIRAL,
            default => null
        };
    }

    public function getPromotionLimit(): ?int
    {
        return match ($this) {
            self::COMMANDER => 25,
            self::CAPTAIN => 15,
            self::COMMODORE => 5,
            self::ADMIRAL => 3,
            default => null
        };
    }

    public function getDescription(int $factionId): string
    {
        return match ($factionId) {
            FactionEnum::FACTION_KLINGON->value => $this->getKlingonDescription(),
            FactionEnum::FACTION_ROMULAN->value => $this->getRomulanDescription(),
            FactionEnum::FACTION_CARDASSIAN->value => $this->getCardassianDescription(),
            FactionEnum::FACTION_FERENGI->value => $this->getFerengiDescription(),
            default => $this->getFederationDescription()
        };
    }

    private function getFederationDescription(): string
    {
        return match ($this) {
            self::CADET => 'Kadett',
            self::CREWMAN => 'Crewman',
            self::ENSIGN => 'Ensign',
            self::JUNIOR_LIEUTENANT => 'Lieutenant Junior Grade',
            self::LIEUTENANT => 'Lieutenant',
            self::LIEUTENANT_COMMANDER => 'Lieutenant Commander',
            self::COMMANDER => 'Commander',
            self::CAPTAIN => 'Captain',
            self::COMMODORE => 'Commodore',
            self::ADMIRAL => 'Admiral'
        };
    }

    private function getKlingonDescription(): string
    {
        return match ($this) {
            self::CADET => 'mangHom',
            self::CREWMAN => 'Crewman',
            self::ENSIGN => 'lagh',
            self::JUNIOR_LIEUTENANT => 'SogHom',
            self::LIEUTENANT => "Sogh'Qov",
            self::LIEUTENANT_COMMANDER => "la' Hom",
            self::COMMANDER => "la'lv",
            self::CAPTAIN => 'HoD',
            self::COMMODORE => 'totlh',
            self::ADMIRAL => "Sa'"
        };
    }

    private function getRomulanDescription(): string
    {
        return match ($this) {
            self::CADET => 'Eredh',
            self::CREWMAN => 'Crewman',
            self::ENSIGN => 'Erein',
            self::JUNIOR_LIEUTENANT => "erei'Arrain",
            self::LIEUTENANT => 'Arrain',
            self::LIEUTENANT_COMMANDER => "khre'Arrain",
            self::COMMANDER => "erei'Riov",
            self::CAPTAIN => 'Riov',
            self::COMMODORE => 'Enarrain',
            self::ADMIRAL => 'Enriov'
        };
    }

    private function getCardassianDescription(): string
    {
        return match ($this) {
            self::CADET => 'Garheç',
            self::CREWMAN => 'Crewman',
            self::ENSIGN => "D'ja",
            self::JUNIOR_LIEUTENANT => 'Kara',
            self::LIEUTENANT => 'Glen',
            self::LIEUTENANT_COMMANDER => 'Gil',
            self::COMMANDER => 'Glinn',
            self::CAPTAIN => 'Gul',
            self::COMMODORE => "Ri'ta Gul",
            self::ADMIRAL => 'Legat'
        };
    }

    private function getFerengiDescription(): string
    {
        return match ($this) {
            self::CADET => "Zok'la",
            self::CREWMAN => 'Crewman',
            self::ENSIGN => 'Zok',
            self::JUNIOR_LIEUTENANT => 'Sub-Letek',
            self::LIEUTENANT => 'Letek',
            self::LIEUTENANT_COMMANDER => 'Sub-Taar',
            self::COMMANDER => 'Taar',
            self::CAPTAIN => 'Daimon',
            self::COMMODORE => 'Zok-Ress',
            self::ADMIRAL => 'Ress'
        };
    }

    public static function getForExpertise(int $expertise): CrewSkillLevelEnum
    {
        return match (true) {
            $expertise < self::CREWMAN->getNeededExpertise() => self::CADET,
            $expertise < self::ENSIGN->getNeededExpertise() => self::CREWMAN,
            $expertise < self::JUNIOR_LIEUTENANT->getNeededExpertise() => self::ENSIGN,
            $expertise < self::LIEUTENANT->getNeededExpertise() => self::JUNIOR_LIEUTENANT,
            $expertise < self::LIEUTENANT_COMMANDER->getNeededExpertise() => self::LIEUTENANT,
            $expertise < self::COMMANDER->getNeededExpertise() => self::LIEUTENANT_COMMANDER,
            $expertise < self::CAPTAIN->getNeededExpertise() => self::COMMANDER,
            $expertise < self::COMMODORE->getNeededExpertise() => self::CAPTAIN,
            $expertise < self::ADMIRAL->getNeededExpertise() => self::COMMODORE,
            default => self::ADMIRAL
        };
    }
}
