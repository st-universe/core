<?php

use PhpTal\Php\TalesInternal;
use PhpTal\TalesRegistry;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\ShipInterface;

function checkPosition(ShipInterface $shipa, ShipInterface $shipb)
{
    if ($shipa->getSystem() !== null) {
        if ($shipb->getSystem() === null || $shipa->getSystem()->getId() !== $shipb->getSystem()->getId()) {
            return false;
        }
        if ($shipa->getSX() != $shipb->getSX() || $shipa->getSY() != $shipb->getSY()) {
            return false;
        }
        return true;
    }
    if ($shipa->getCX() != $shipb->getCX() || $shipa->getCY() != $shipb->getCY()) {
        return false;
    }
    return true;
}

function checkColonyPosition(ColonyInterface $col, ShipInterface $ship)
{
    if ($col->getSystemsId() != $ship->getSystem()->getId()) {
        return false;
    }
    if ($col->getSX() != $ship->getSX() || $col->getSY() != $ship->getSY()) {
        return false;
    }
    return true;
}

function compareBuildings(PlanetFieldInterface$a, PlanetFieldInterface $b)
{
    if ($a->getBuilding()->getId() == $b->getBuilding()->getId()) {
        return $a->getId() > $b->getId();
    }
    return strcmp($a->getBuilding()->getName(), $b->getBuilding()->getName());
}

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
        return 'date(\'d.m.Y H:i\', ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
    }
);
TalesRegistry::registerPrefix(
    'date',
    function ($src, $nothrow): string {
        return 'date(\'d.m.Y\', ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
    }
);
TalesRegistry::registerPrefix(
    'nl2br',
    function ($src, $nothrow): string {
        return 'nl2br(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
    }
);
TalesRegistry::registerPrefix(
    'formatSeconds',
    function ($src, $nothrow): string {
        return '\Stu\Module\Tal\TalHelper::formatSeconds(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
    }
);
