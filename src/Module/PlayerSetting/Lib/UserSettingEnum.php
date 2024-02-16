<?php

namespace Stu\Module\PlayerSetting\Lib;

enum UserSettingEnum: string
{
    case AVATAR = 'avatar';
    case RGB_CODE = 'rgb_code';
    case EMAIL_NOTIFICATION = 'email_notification';
    case STORAGE_NOTIFICATION = 'storage_notification';
    case SAVE_LOGIN = 'save_login';
    case DEFAULT_VIEW = 'default_view';
    case SHOW_ONLINE_STATUS = 'show_online_status';
    case SHOW_PM_READ_RECEIPT = 'show_pm_read_receipt';
    case FLEET_FIXED_DEFAULT = 'fleet_fixed_default';
    case WARPSPLIT_AUTO_CARRYOVER_DEFAULT = 'warpsplit_auto_carryover_default';
    case RPG_BEHAVIOR = 'rpg_behavior';
    case CSS_COLOR_SHEET = 'css_color_sheet';
    case SHOW_PIRATE_HISTORY_ENTRYS = 'show_pirate_history_entrys';

    /**
     * returns true, if the setting has it's own action controller
     */
    public function isDistinctSetting(): bool
    {
        if (
            $this === self::AVATAR
            || $this === self::RGB_CODE
        ) {
            return true;
        }

        return false;
    }
}