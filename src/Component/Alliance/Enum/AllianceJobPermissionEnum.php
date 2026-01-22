<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Enum;

enum AllianceJobPermissionEnum: int
{
    case FOUNDER = 1;
    case SUCCESSOR = 2;
    case DIPLOMATIC = 3;
    case CREATE_AGREEMENTS = 4;
    case EDIT_DIPLOMATIC_DOCUMENTS = 5;
    case ALLIANCE_LEADERSHIP = 6;
    case EDIT_ALLIANCE = 7;
    case MANAGE_APPLICATIONS = 8;
    case MANAGE_JOBS = 9;
    case VIEW_COLONIES = 10;
    case VIEW_MEMBER_DATA = 11;
    case VIEW_SHIPS = 12;
    case VIEW_ALLIANCE_STORAGE = 13;
    case VIEW_ALLIANCE_HISTORY = 14;

    public function getDescription(): string
    {
        return match ($this) {
            self::FOUNDER => 'Präsident',
            self::SUCCESSOR => 'Vize',
            self::DIPLOMATIC => 'Diplomat',
            self::CREATE_AGREEMENTS => 'Kann Abkommen erstellen',
            self::EDIT_DIPLOMATIC_DOCUMENTS => 'Kann diplomatische Vertragswerke bearbeiten',
            self::ALLIANCE_LEADERSHIP => 'Gehört zur Allianzleitung',
            self::EDIT_ALLIANCE => 'Kann Allianz editieren',
            self::MANAGE_APPLICATIONS => 'Kann Bewerbungen bearbeiten',
            self::MANAGE_JOBS => 'Kann Jobs verwalten',
            self::VIEW_COLONIES => 'Kann Kolonien einsehen',
            self::VIEW_MEMBER_DATA => 'Kann Mitgliederdaten einsehen',
            self::VIEW_SHIPS => 'Kann Schiffe einsehen',
            self::VIEW_ALLIANCE_STORAGE => 'Kann Allianzdepots einsehen',
            self::VIEW_ALLIANCE_HISTORY => 'Kann Allianz-History einsehen'
        };
    }

    public function isEditable(): bool
    {
        return match ($this) {
            self::FOUNDER => false,
            default => true
        };
    }

    public function getParentPermission(): ?self
    {
        return match ($this) {
            self::CREATE_AGREEMENTS, self::EDIT_DIPLOMATIC_DOCUMENTS => self::DIPLOMATIC,
            self::ALLIANCE_LEADERSHIP, self::EDIT_ALLIANCE, self::MANAGE_APPLICATIONS,
            self::MANAGE_JOBS, self::VIEW_COLONIES, self::VIEW_MEMBER_DATA,
            self::VIEW_SHIPS, self::VIEW_ALLIANCE_STORAGE, self::VIEW_ALLIANCE_HISTORY => self::SUCCESSOR,
            default => null
        };
    }

    public function isParentPermission(): bool
    {
        return in_array($this, [self::FOUNDER, self::SUCCESSOR, self::DIPLOMATIC]);
    }

    /**
     * @return array<self>
     */
    public function getChildPermissions(): array
    {
        return match ($this) {
            self::DIPLOMATIC => [
                self::CREATE_AGREEMENTS,
                self::EDIT_DIPLOMATIC_DOCUMENTS
            ],
            self::SUCCESSOR => [
                self::ALLIANCE_LEADERSHIP,
                self::EDIT_ALLIANCE,
                self::MANAGE_APPLICATIONS,
                self::MANAGE_JOBS,
                self::VIEW_COLONIES,
                self::VIEW_MEMBER_DATA,
                self::VIEW_SHIPS,
                self::VIEW_ALLIANCE_STORAGE,
                self::VIEW_ALLIANCE_HISTORY
            ],
            default => []
        };
    }

    public function getCategory(): string
    {
        return match ($this) {
            self::DIPLOMATIC, self::CREATE_AGREEMENTS, self::EDIT_DIPLOMATIC_DOCUMENTS => 'Diplomatische Rechte',
            self::SUCCESSOR, self::ALLIANCE_LEADERSHIP, self::EDIT_ALLIANCE,
            self::MANAGE_APPLICATIONS, self::MANAGE_JOBS, self::VIEW_COLONIES,
            self::VIEW_MEMBER_DATA, self::VIEW_SHIPS, self::VIEW_ALLIANCE_STORAGE,
            self::VIEW_ALLIANCE_HISTORY => 'Verwaltungsrechte',
            default => 'Allgemein'
        };
    }
}
