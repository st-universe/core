<?php

declare(strict_types=1);

namespace Stu\Module\Tal;

use JBBCode\Parser;
use PhpTal\Php\TalesInternal;
use PhpTal\TalesRegistry;
use Psr\Container\ContainerInterface;
use Stu\Module\Colony\Lib\PlanetFieldTypeRetrieverInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\Tal\Exception\DiContainerNotSetException;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;

final class TalHelper
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
            throw new DiContainerNotSetException();
        }

        return self::$dic;
    }

    public static function formatProductionValue(int $value): string
    {
        if ($value > 0) {
            return sprintf('<span class="positive">+%d</span>', $value);
        } elseif ($value < 0) {
            return sprintf('<span class="negative">%d</span>', $value);
        }
        return (string) $value;
    }

    public static function addPlusCharacter(string $value): string
    {
        if ($value <= 0) {
            return $value;
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
        $time = (int) $time;
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
        return number_format((float) $number, 0, '', '.');
    }

    public static function getPlanetFieldTypeDescription(
        int $fieldTypeId
    ): string {
        return self::getDic()->get(PlanetFieldTypeRetrieverInterface::class)->getDescription($fieldTypeId);
    }

    public static function getPlanetFieldTitle(
        PlanetFieldInterface $planetField
    ): string {
        $fieldTypeName = self::getPlanetFieldTypeDescription($planetField->getFieldType());

        $building = $planetField->getBuilding();

        if ($building === null) {
            $terraFormingState = null;
            $host = $planetField->getHost();
            if ($host instanceof ColonyInterface) {
                $terraFormingState = self::getDic()->get(ColonyTerraformingRepositoryInterface::class)->getByColonyAndField(
                    $host->getId(),
                    $planetField->getId()
                );
            }
            if ($terraFormingState !== null) {
                return sprintf(
                    '%s läuft bis %s',
                    $terraFormingState->getTerraforming()->getDescription(),
                    date('d.m.Y H:i', $terraFormingState->getFinishDate())
                );
            }
            return $fieldTypeName;
        }

        if ($planetField->isUnderConstruction()) {
            return sprintf(
                'In Bau: %s auf %s - Fertigstellung: %s',
                $building->getName(),
                $fieldTypeName,
                date('d.m.Y H:i', $planetField->getBuildtime())
            );
        }
        if (!$planetField->isActivateable()) {
            return $building->getName() . " auf " . $fieldTypeName;
        }

        if ($planetField->isActive()) {
            if ($planetField->isDamaged()) {
                return $building->getName() . " (aktiviert, beschädigt) auf " . $fieldTypeName;
            }
            return $building->getName() . " (aktiviert) auf " . $fieldTypeName;
        }

        if ($planetField->hasHighDamage()) {
            return $building->getName() . " (stark beschädigt) auf " . $fieldTypeName;
        }

        return $building->getName() . " (deaktiviert) auf " . $fieldTypeName;
    }

    /**
     * Registers global available tal methods
     */
    public static function register(ContainerInterface $dic): void
    {
        self::setDic($dic);

        TalesRegistry::registerPrefix(
            'clmodeDescription',
            fn ($src, $nothrow): string => '\Stu\Module\Tal\TalHelper::getContactListModeDescription((int) ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')'
        );
        TalesRegistry::registerPrefix(
            'addPlusCharacter',
            fn ($src, $nothrow): string => '\Stu\Module\Tal\TalHelper::addPlusCharacter((int)' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')'
        );
        TalesRegistry::registerPrefix(
            'isPositive',
            fn ($src, $nothrow): string => '(int) ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ' > 0'
        );
        TalesRegistry::registerPrefix(
            'isNegative',
            fn ($src, $nothrow): string => '(int) ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ' < 0'
        );
        TalesRegistry::registerPrefix(
            'numberWithThousandSeperator',
            fn ($src, $nothrow): string => '\Stu\Module\Tal\TalHelper::getNumberWithThousandSeperator(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')'
        );
        TalesRegistry::registerPrefix(
            'bbcode',
            fn ($src, $nothrow): string => '\Stu\Module\Tal\TalHelper::getBBCodeParser()->parse(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')->getAsHtml()'
        );
        TalesRegistry::registerPrefix(
            'bbcode2txt',
            fn ($src, $nothrow): string => '\Stu\Module\Tal\TalHelper::getBBCodeParser()->parse(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')->getAsText()'
        );
        TalesRegistry::registerPrefix(
            'jsquote',
            fn ($src, $nothrow): string => '\Stu\Module\Tal\TalHelper::jsquote(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')'
        );
        TalesRegistry::registerPrefix(
            'datetime',
            fn ($src, $nothrow): string => 'date(\'d.m.\', ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ') . (date("Y", ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')+' . StuTime::STU_YEARS_IN_FUTURE_OFFSET . ') . " " . date("H:i", ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')'
        );
        TalesRegistry::registerPrefix(
            'date',
            fn ($src, $nothrow): string => 'date(\'d.m.\', ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ') . (date("Y", ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')+' . StuTime::STU_YEARS_IN_FUTURE_OFFSET . ')'
        );
        TalesRegistry::registerPrefix(
            'nl2br',
            fn ($src, $nothrow): string => 'nl2br(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')'
        );
        TalesRegistry::registerPrefix(
            'nl2brBbCode',
            fn ($src, $nothrow): string => 'nl2br(\Stu\Module\Tal\TalHelper::getBBCodeParser()->parse(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')->getAsHtml())'
        );
        TalesRegistry::registerPrefix(
            'formatSeconds',
            fn ($src, $nothrow): string => '\Stu\Module\Tal\TalHelper::formatSeconds(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')'
        );
        TalesRegistry::registerPrefix(
            'planetFieldTypeDescription',
            fn ($src, $nothrow): string => '\Stu\Module\Tal\TalHelper::getPlanetFieldTypeDescription(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')'
        );
        TalesRegistry::registerPrefix(
            'planetFieldTitle',
            fn ($src, $nothrow): string => '\Stu\Module\Tal\TalHelper::getPlanetFieldTitle(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')'
        );
        TalesRegistry::registerPrefix(
            'formatProductionValue',
            fn ($src, $nothrow): string => '\Stu\Module\Tal\TalHelper::formatProductionValue(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')'
        );
    }
}
