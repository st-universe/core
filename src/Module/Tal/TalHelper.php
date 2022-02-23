<?php

declare(strict_types=1);

namespace Stu\Module\Tal;

use JBBCode\Parser;
use Stu\Module\Message\Lib\ContactListModeEnum;

final class TalHelper implements TalHelperInterface
{

    public static function addPlusCharacter(string $value): string
    {
        if ($value <= 0) {
            return (string) $value;
        }
        return sprintf('+%d', $value);
    }

    public static function getContactListModeDescription(int $mode): string
    {
        switch ($mode) {
            case ContactListModeEnum::CONTACT_FRIEND:
                return _('Freund');
            case ContactListModeEnum::CONTACT_ENEMY:
                return _('Feind');
            case ContactListModeEnum::CONTACT_NEUTRAL:
                return _('Neutral');
        }
        return '';
    }

    public static function getBBCodeParser(): Parser
    {
        global $container;

        return $container->get(Parser::class);
    }

    public static function jsquote(string $str): string
    {
        return str_replace(
            [
                "\\",
                "'",
            ],
            [
                "\\\\",
                "\\'",
            ],
            $str
        );
    }

    public static function formatSeconds(string $time): string
    {
        $h = floor($time / 3600);
        $time -= $h * 3600;
        $m = floor($time / 60);
        $time -= $m * 60;

        $ret = '';
        if ($h > 0) {
            $ret .= $h . 'h';
        }
        if ($m > 0) {
            $ret .= ' ' . $m . 'm';
        }
        if ($time > 0) {
            $ret .= ' ' . $time . 's';
        }
        return $ret;
    }

    public static function getNumberWithThousandSeperator(int $number): string
    {
        return number_format(floatval($number), 0, '', '.');
    }
}
