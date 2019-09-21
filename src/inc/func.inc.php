<?php

use JBBCode\Parser;
use PhpTal\Php\TalesInternal;
use PhpTal\TalesRegistry;
use Stu\Lib\DbInterface;
use Stu\Module\Communication\Lib\ContactListModeEnum;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;

function dbSafe(&$string)
{
    return addslashes(str_replace("\"", "", $string));
}

class Tuple
{

    private $var = null;
    private $value = null;

    function __construct($var, $value)
    {
        $this->var = $var;
        $this->value = $value;
    }

    function getVar()
    {
        return $this->var;
    }

    function getValue()
    {
        return $this->value;
    }
}

function checkPosition(&$shipa, &$shipb)
{
    if ($shipa->isInSystem()) {
        if (!$shipb->isInSystem()) {
            return false;
        }
        if ($shipa->getSystemsId() != $shipb->getSystemsId()) {
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

function checkColonyPosition($col, $ship)
{
    if ($col->getSystemsId() != $ship->getSystemsId()) {
        return false;
    }
    if ($col->getSX() != $ship->getSX() || $col->getSY() != $ship->getSY()) {
        return false;
    }
    return true;
}

function getStorageBar(&$bar, $file, $amount, &$sum)
{
    if ($sum < $amount) {
        return;
    }
    $mod = floor($sum / $amount);
    for ($i = 1; $i <= $mod; $i++) {
        $bar[] = new Tuple($file . '_' . $amount, false);
    }
    $sum -= $mod * $amount;
}

function compareBuildings(&$a, &$b)
{
    if ($a->getBuilding()->getId() == $b->getBuilding()->getId()) {
        return $a->getId() > $b->getId();
    }
    return strcmp($a->getBuilding()->getName(), $b->getBuilding()->getName());
}

/**
 */
function tidyString(&$string)
{ #{{{
    return str_replace(['<', '>', '&gt;', '&lt;'], '', $string);
} # }}}

function renderResearchStatusBar($points, &$maxpoints)
{
    $pro = getPercentage($points, $maxpoints);
    $bar = getStatusBar(STATUSBAR_BLUE, ceil($pro / 2) * 2, 'Fortschritt: ' . $points . '/' . $maxpoints);
    if ($pro < 100) {
        $bar .= getStatusBar(STATUSBAR_GREY, floor((100 - $pro) / 2) * 2, 'Fortschritt: ' . $points . '/' . $maxpoints);
    }
    return $bar;
}

function renderShieldStatusBar(&$active, &$shields, &$maxshields)
{
    $pro = getPercentage($shields, $maxshields);
    $bar = getStatusBar(($active == 1 ? STATUSBAR_BLUE : STATUSBAR_DARKBLUE), ceil($pro / 2),
        'Schilde: ' . $shields . '/' . $maxshields);
    if ($pro < 100) {
        $bar .= getStatusBar(STATUSBAR_GREY, floor((100 - $pro) / 2), 'Schilde: ' . $shields . '/' . $maxshields);
    }
    return $bar;
}

function getStatusBar($color, $amount, $title = '')
{
    return '<img src="assets/bars/balken.png" style="background-color: #' . $color . ';height: 12px; width:' . round($amount) . 'px;" title="' . $title . '" />';
}

function getPercentageStatusBar($color, $amount, $maxamount)
{
    $pro = getPercentage($amount, $maxamount);
    $bar = getStatusBar($color, ceil($pro / 2), 'Status: ' . $pro . '%');
    if ($pro < 100) {
        $bar .= getStatusBar(STATUSBAR_GREY, floor((100 - $pro) / 2), 'Status: ' . $pro . '%');
    }
    return $bar;
}

function getPercentage($val, $maxval)
{
    if ($val > $maxval) {
        $val = $maxval;
    }
    return max(0, @round((100 / $maxval) * min($val, $maxval)));
}

function renderHuellStatusBar(&$huell, &$maxhuell)
{
    $pro = getPercentage($huell, $maxhuell);
    $bar = getStatusBar(STATUSBAR_GREEN, ceil($pro / 2), 'Hülle: ' . $huell . '/' . $maxhuell);
    if ($pro < 100) {
        $bar .= getStatusBar(STATUSBAR_GREY, floor((100 - $pro) / 2), 'Hülle: ' . $huell . '/' . $maxhuell);
    }
    return $bar;
}

function renderStorageStatusBar(&$storage, &$maxstorage)
{
    $pro = getPercentage($storage, $maxstorage);
    $bar = getStatusBar(STATUSBAR_GREEN, ceil($pro / 2), 'Lager: ' . $storage . '/' . $maxstorage . ' (' . $pro . '%)');
    if ($pro < 100) {
        $bar .= getStatusBar(STATUSBAR_GREY, floor((100 - $pro) / 2),
            'Lager: ' . $storage . '/' . $maxstorage . ' (' . $pro . '%)');
    }
    return $bar;
}

function renderEpsStatusBar(&$eps, &$maxeps)
{
    $pro = getPercentage($eps, $maxeps);
    $bar = getStatusBar(STATUSBAR_YELLOW, ceil($pro / 2), 'Energie: ' . $eps . '/' . $maxeps);
    if ($pro < 100) {
        $bar .= getStatusBar(STATUSBAR_GREY, floor((100 - $pro) / 2), 'Energie: ' . $eps . '/' . $maxeps);
    }
    return $bar;
}

function getOnlineStatus($online)
{
    if ($online) {
        return "online";
    }
    return "offline";
}

function generatePassword($length = 6)
{

    $dummy = array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'), ['#', '&', '@', '$', '_', '%', '?', '+']);

    mt_srand((double)microtime() * 1000000);

    for ($i = 1; $i <= (count($dummy) * 2); $i++) {
        $swap = mt_rand(0, count($dummy) - 1);
        $tmp = $dummy[$swap];
        $dummy[$swap] = $dummy[0];
        $dummy[0] = $tmp;
    }

    return substr(implode('', $dummy), 0, $length);
}

function formatSeconds($time)
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

function parseDateTime($value)
{
    return date("d.m.Y H:i", $value);
}

function databaseScan($database_id, $user_id)
{
    if ($database_id == 0) {
        return;
    }

    // @todo refactor
    global $container;

    /**
     * @var DatabaseEntryInterface $entry
     */
    $entry = $container->get(DatabaseEntryRepositoryInterface::class)->find($database_id);
    $databaseUserRepository = $container->get(DatabaseUserRepositoryInterface::class);

    $userEntry = $databaseUserRepository->prototype()
        ->setUserId($user_id)
        ->setDatabaseEntry($entry)
        ->setDate(time());

    $databaseUserRepository->save($userEntry);

    return sprintf(_("Neuer Datenbankeintrag: %s (+%d Punkte)"), $entry->getDescription(),
        $entry->getCategory()->getPoints());
}

/**
 */
function calculateModuleValue($rump, $module, $callback = 'aggi', $value = false)
{ #{{{
    if (!$value) {
        $value = $rump->$callback();
    }
    if ($rump->getModuleLevel() > $module->getLevel()) {
        return round($value - $value / 100 * $module->getDowngradeFactor());
    }
    if ($rump->getModuleLevel() < $module->getLevel()) {
        return round($value + $value / 100 * $module->getUpgradeFactor());
    }
    return $value;
} # }}}

/**
 */
function calculateDamageImpact(ShipRumpInterface $rump, ModuleInterface $module)
{ #{{{
    if ($rump->getModuleLevel() > $module->getLevel()) {
        return '-' . $module->getDowngradeFactor() . '%';
    }
    if ($rump->getModuleLevel() < $module->getLevel()) {
        return '+' . $module->getUpgradeFactor() . '%';
    }
    return _('Normal');
} # }}}

/**
 */
function calculateEvadeChance($rump, $module)
{ #{{{
    $base = $rump->getEvadeChance();
    if ($rump->getModuleLevel() > $module->getLevel()) {
        $value = (1 - $base / 100) * 1 / (1 - $module->getDowngradeFactor() / 100);
    } elseif ($rump->getModuleLevel() < $module->getLevel()) {
        $value = (1 - $base / 100) * 1 / (1 + $module->getUpgradeFactor() / 100);
    } else {
        return $base;
    }
    return round((1 - $value) * 100);
} # }}}

function printBackTrace()
{
    echo '<div style="background-color:rgb(240,240,240)">';
    foreach (debug_backtrace() as $bt) {
        printf('<pre>%s%s%s(%s) </pre>'
            . '<blockquote>%s:%d</blockquote>',
            isset($bt['class']) ? $bt['class'] : "",
            isset($bt['type']) ? $bt['type'] : "",
            $bt['function'], join(", ", array_map("strval", $bt['args'])),
            $bt['file'], $bt['line']
        );

    }
    echo "</div>";
}

/**
 */
function getContactlistModes()
{ #{{{
    return [
        ContactListModeEnum::CONTACT_FRIEND => ["mode" => ContactListModeEnum::CONTACT_FRIEND, "name" => _("Freund")],
        ContactListModeEnum::CONTACT_ENEMY => ["mode" => ContactListModeEnum::CONTACT_ENEMY, "name" => _("Feind")],
        ContactListModeEnum::CONTACT_NEUTRAL => ["mode" => ContactListModeEnum::CONTACT_NEUTRAL, "name" => _("Neutral")],
    ];
} # }}}
/**
 */
function getDefaultTechs()
{ #{{{
    return [
        RESEARCH_START_FEDERATION,
        RESEARCH_START_ROMULAN,
        RESEARCH_START_KLINGON,
        RESEARCH_START_CARDASSIAN,
        RESEARCH_START_FERENGI,
        RESEARCH_START_EMPIRE,
    ];
} # }}}
/**
 */
function isSystemUser($userId)
{ #{{{
    $user = [USER_NOONE];
    return in_array($userId, $user);
} # }}}

/**
 */
function getModuleLevelClass(ShipRumpInterface $rump, $module)
{ #{{{
    if ($rump->getModuleLevels()->{'getModuleLevel' . $module->getModule()->getType()}() > $module->getModule()->getLevel()) {
        return 'module_positive';
    }
    if ($rump->getModuleLevels()->{'getModuleLevel' . $module->getModule()->getType()}() < $module->getModule()->getLevel()) {
        return 'module_negative';
    }
    return '';
} # }}}

/**
 *
 */
function jsquote($str)
{ #{{{
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
} #}}}

function &DB()
{
    static $DB = null;
    if ($DB === null) {
        global $container;
        return $container->get(DbInterface::class);
    }
    return $DB;
}

function currentUser(): User
{
    static $currentUser = null;
    if ($currentUser === null) {
        global $_SESSION;
        $currentUser = ResourceCache()->getUser($_SESSION['uid']);
    }
    return $currentUser;
}

function BBCode(): Parser
{
    global $container;

    return $container->get(Parser::class);
}

function getContactListModeDescription(int $mode): string
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

function addPlusCharacter(int $value): string
{
   if ($value <= 0) {
       return (string) $value;
   }
   return sprintf('+%d', $value);
}

TalesRegistry::registerPrefix(
    'clmodeDescription',
    function ($src, $nothrow): string {
        return 'getContactListModeDescription((int) ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
    }
);
TalesRegistry::registerPrefix(
    'addPlusCharacter',
    function ($src, $nothrow): string {
        return 'addPlusCharacter((int)' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
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
        return 'BBCode()->parse(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')->getAsHtml()';
    }
);
TalesRegistry::registerPrefix(
    'bbcode2txt',
    function ($src, $nothrow): string {
        return 'BBCode()->parse(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')->getAsText()';
    }
);
TalesRegistry::registerPrefix(
    'jsquote',
    function ($src, $nothrow): string {
        return 'jsquote(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
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
        return 'formatSeconds(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
    }
);
