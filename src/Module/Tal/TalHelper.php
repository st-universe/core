<?php

declare(strict_types=1);

namespace Stu\Module\Tal;

use JBBCode\Parser;
use PhpTal\Php\TalesInternal;
use PhpTal\TalesRegistry;
use Psr\Container\ContainerInterface;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\Tal\Exception\DiContainerNotSetException;

final class TalHelper implements TalHelperInterface
{
    private static ?ContainerInterface $dic = null;

    private static function setDic(ContainerInterface $dic): void
    {
        self::$dic = $dic;
    }

    /**
     * @throws DiContainerNotSetException
     */
    private static function getDic(): ContainerInterface
    {
        if (self::$dic === null) {
            throw new Exception\DiContainerNotSetException();
        }

        return self::$dic;
    }

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
        return self::getDic()->get(Parser::class);
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

    /**
     * Registers global available tal methods
     */
    public static function register(ContainerInterface $dic): void
    {
        self::setDic($dic);

        TalesRegistry::registerPrefix(
            'clmodeDescription',
            function ($src, $nothrow): string {
                return '\Stu\Module\Tal\TalHelper::getContactListModeDescription((int) ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
            }
        );
        TalesRegistry::registerPrefix(
            'addPlusCharacter',
            function ($src, $nothrow): string {
                return '\Stu\Module\Tal\TalHelper::addPlusCharacter((int)' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
            }
        );
        TalesRegistry::registerPrefix(
            'isPositive',
            function ($src, $nothrow): string {
                return '(int) ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ' > 0';
            }
        );
        TalesRegistry::registerPrefix(
            'isNegative',
            function ($src, $nothrow): string {
                return '(int) ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ' < 0';
            }
        );
        TalesRegistry::registerPrefix(
            'numberWithThousandSeperator',
            function ($src, $nothrow): string {
                return '\Stu\Module\Tal\TalHelper::getNumberWithThousandSeperator(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
            }
        );
        TalesRegistry::registerPrefix(
            'bbcode',
            function ($src, $nothrow): string {
                return '\Stu\Module\Tal\TalHelper::getBBCodeParser()->parse(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')->getAsHtml()';
            }
        );
        TalesRegistry::registerPrefix(
            'bbcode2txt',
            function ($src, $nothrow): string {
                return '\Stu\Module\Tal\TalHelper::getBBCodeParser()->parse(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')->getAsText()';
            }
        );
        TalesRegistry::registerPrefix(
            'jsquote',
            function ($src, $nothrow): string {
                return '\Stu\Module\Tal\TalHelper::jsquote(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
            }
        );
        TalesRegistry::registerPrefix(
            'datetime',
            function ($src, $nothrow): string {
                return 'date(\'d.m.\', ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ') . (date("Y", ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')+370) . " " . date("H:i", ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
            }
        );
        TalesRegistry::registerPrefix(
            'date',
            function ($src, $nothrow): string {
                return 'date(\'d.m.\', ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ') . (date("Y", ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')+370)';
            }
        );
        TalesRegistry::registerPrefix(
            'nl2br',
            function ($src, $nothrow): string {
                return 'nl2br(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
            }
        );
        TalesRegistry::registerPrefix(
            'nl2brBbCode',
            function ($src, $nothrow): string {
                return 'nl2br(\Stu\Module\Tal\TalHelper::getBBCodeParser()->parse(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')->getAsHtml())';
            }
        );
        TalesRegistry::registerPrefix(
            'formatSeconds',
            function ($src, $nothrow): string {
                return '\Stu\Module\Tal\TalHelper::formatSeconds(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
            }
        );
    }
}
