<?php

namespace Stu\Module\PlayerSetting\Lib;

use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Orm\Entity\User;

enum UserSettingEnum: string
{
    // DISTINCT
    case AVATAR = 'avatar';
    case RGB_CODE = 'rgb_code';

        // NON DISTINCT
    case EMAIL_NOTIFICATION = 'email_notification';
    case SAVE_LOGIN = 'save_login';
    case STORAGE_NOTIFICATION = 'storage_notification';
    case SHOW_ONLINE_STATUS = 'show_online_status';
    case SHOW_PM_READ_RECEIPT = 'show_pm_read_receipt';
    case FLEET_FIXED_DEFAULT = 'fleet_fixed_default';
    case WARPSPLIT_AUTO_CARRYOVER_DEFAULT = 'warpsplit_auto_carryover_default';
    case SHOW_PIRATE_HISTORY_ENTRYS = 'show_pirate_history_entrys';
    case INBOX_MESSENGER_STYLE = 'inbox_messenger_style';

    case DEFAULT_VIEW = 'default_view';
    case RPG_BEHAVIOR = 'rpg_behavior';
    case CSS_COLOR_SHEET = 'css_color_sheet';

    /**
     * returns true, if the setting has it's own action controller
     */
    public function isDistinctSetting(): bool
    {
        return $this === self::AVATAR
            || $this === self::RGB_CODE;
    }

    public function isNullable(): bool
    {
        return $this === self::DEFAULT_VIEW;
    }

    public function getTitle(): string
    {
        return match ($this) {
            self::AVATAR => 'Avatar',
            self::RGB_CODE => 'Territorialfarbe',
            default => ''
        };
    }

    public function getDescription(): mixed
    {
        return match ($this) {
            self::AVATAR => 'Hier kannst Du einen Avatar
                            hochladen der in Deinem Profil angezeigt wird. Dieses Bild sollte
                            150x150 Pixel groß sein und im PNG-Format vorliegen.',
            self::RGB_CODE => 'Dieser RGB-Farbcode kennzeichnet Kartenfelder, die dem Spieler zugeordnet
                                werden können. Beispiel: #9ab5ef',
            self::EMAIL_NOTIFICATION => 'Emailbenachrichtigung bei privaten Nachrichten',
            self::STORAGE_NOTIFICATION => 'Nachricht bei vollen Lagern zum Tick',
            self::SAVE_LOGIN => 'Login nicht speichern (Ideal an fremden Computern)',
            self::DEFAULT_VIEW => 'Auswahl der Startseite, welche nach dem Login angezeigt werden soll.',
            self::SHOW_ONLINE_STATUS => 'Online Status öffentlich',
            self::SHOW_PM_READ_RECEIPT => 'Lesebestätigungen bei PMs anzeigen (nur wenn sowohl Sender als auch Empfänger den Haken gesetzt haben)',
            self::FLEET_FIXED_DEFAULT => 'Flotten standardmäßig fixieren, sodass keine Schiffe beim Flug verloren gehen können.',
            self::WARPSPLIT_AUTO_CARRYOVER_DEFAULT => 'Führt dazu, dass überschüssige Reaktorleistung bei Schiffen automatisch an die übrigen Systeme transferiert wird.',
            self::RPG_BEHAVIOR => 'Information für andere Spieler ob man selbst gern Rollenspiel betreibt.',
            self::CSS_COLOR_SHEET => 'Auswahl der Interface-Farben.',
            self::SHOW_PIRATE_HISTORY_ENTRYS => 'Zeigt einträge in der History an die durch Interaktion mit dem Piraten NPC entstanden sind.',
            self::INBOX_MESSENGER_STYLE => 'Nachrichteneingang des "Persönlich"-Ordners ist wie bei einem Messenger nach Kontakten gruppiert.'
        };
    }

    public function getTemplate(): string
    {
        return match ($this) {
            self::CSS_COLOR_SHEET,
            self::RPG_BEHAVIOR,
            self::DEFAULT_VIEW  => 'html/user/settings/enumUserSetting.twig',
            self::AVATAR        => 'html/user/settings/avatarUserSetting.twig',
            self::RGB_CODE      => 'html/user/settings/rgbCodeUserSetting.twig',
            default             => 'html/user/settings/booleanUserSetting.twig'
        };
    }

    public function getUserValue(User $user, UserSettingsProviderInterface $settingsProvider): mixed
    {
        return match ($this) {
            self::AVATAR => $settingsProvider->getAvatar($user),
            self::RGB_CODE => $settingsProvider->getRgbCode($user),
            self::EMAIL_NOTIFICATION => $settingsProvider->isEmailNotification($user),
            self::STORAGE_NOTIFICATION => $settingsProvider->isStorageNotification($user),
            self::SAVE_LOGIN => $settingsProvider->isSaveLogin($user),
            self::DEFAULT_VIEW => $settingsProvider->getDefaultView($user),
            self::SHOW_ONLINE_STATUS => $settingsProvider->isShowOnlineState($user),
            self::SHOW_PM_READ_RECEIPT => $settingsProvider->isShowPmReadReceipt($user),
            self::FLEET_FIXED_DEFAULT => $settingsProvider->getFleetFixedDefault($user),
            self::WARPSPLIT_AUTO_CARRYOVER_DEFAULT => $settingsProvider->getWarpsplitAutoCarryoverDefault($user),
            self::RPG_BEHAVIOR => $settingsProvider->getRpgBehavior($user),
            self::CSS_COLOR_SHEET => $settingsProvider->getCss($user),
            self::SHOW_PIRATE_HISTORY_ENTRYS => $settingsProvider->isShowPirateHistoryEntrys($user),
            self::INBOX_MESSENGER_STYLE => $settingsProvider->isInboxMessengerStyle($user)
        };
    }

    /** @return array<self> */
    public static function getDistinct(): array
    {
        return array_filter(self::cases(), fn(UserSettingEnum $type): bool => $type->isDistinctSetting());
    }

    /** @return array<self> */
    public static function getNonDistinct(): array
    {
        return array_filter(self::cases(), fn(UserSettingEnum $type): bool => !$type->isDistinctSetting());
    }
}
