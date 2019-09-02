<?php

use JBBCode\Parser;
use PhpTal\Php\TalesInternal;
use PhpTal\TalesRegistry;
use Stu\Lib\DbInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;

function dbSafe(&$string) {
	return addslashes(str_replace("\"","",$string));
}
class Tuple {

	private $var = NULL;
	private $value = NULL;

	function __construct($var,$value) {
		$this->var = $var;
		$this->value = $value;
	}

	function getVar() {
		return $this->var;
	}
	
	function getValue() {
		return $this->value;
	}
}
function checkPosition(&$shipa,&$shipb) {
	if ($shipa->isInSystem()) {
		if (!$shipb->isInSystem()) {
			return FALSE;
		}
		if ($shipa->getSystemsId() != $shipb->getSystemsId()) {
			return FALSE;
		}
		if ($shipa->getSX() != $shipb->getSX() || $shipa->getSY() != $shipb->getSY()) {
			return FALSE;
		}
		return TRUE;
	}
	if ($shipa->getCX() != $shipb->getCX() || $shipa->getCY() != $shipb->getCY()) {
		return FALSE;
	}
	return TRUE;
}
function checkColonyPosition($col,$ship) {
	if ($col->getSystemsId() != $ship->getSystemsId()) {
		return FALSE;
	}
	if ($col->getSX() != $ship->getSX() || $col->getSY() != $ship->getSY()) {
		return FALSE;
	}
	return TRUE;
}
function getStorageBar(&$bar,$file,$amount,&$sum) {
	if ($sum < $amount) {
		return;
	}
	$mod = floor($sum/$amount);
	for ($i=1;$i<=$mod;$i++) {
		$bar[] = new Tuple($file.'_'.$amount,FALSE);
	}
	$sum -= $mod*$amount;
}
function comparePMCategories(&$a,&$b) {
	return strcmp($a->getSort(), $b->getSort());
}
function compareBuildings(&$a,&$b) {
	if ($a->getBuilding()->getId() == $b->getBuilding()->getId()) {
		return $a->getId() > $b->getId();
	}
	return strcmp($a->getBuilding()->getName(), $b->getBuilding()->getName());
}
/**
 */
function tidyString(&$string) { #{{{
	return str_replace(array('<','>','&gt;','&lt;'),'',$string);
} # }}}

function renderResearchStatusBar($points,&$maxpoints) {
	$pro = getPercentage($points,$maxpoints);
	$bar = getStatusBar(STATUSBAR_BLUE,ceil($pro/2)*2,'Fortschritt: '.$points.'/'.$maxpoints);
	if ($pro < 100) {
		$bar .= getStatusBar(STATUSBAR_GREY,floor((100-$pro)/2)*2,'Fortschritt: '.$points.'/'.$maxpoints);
	}
	return $bar;
}
function renderShieldStatusBar(&$active,&$shields,&$maxshields) {
	$pro = getPercentage($shields,$maxshields);
	$bar = getStatusBar(($active == 1 ? STATUSBAR_BLUE : STATUSBAR_DARKBLUE),ceil($pro/2),'Schilde: '.$shields.'/'.$maxshields);
	if ($pro < 100) {
		$bar .= getStatusBar(STATUSBAR_GREY,floor((100-$pro)/2),'Schilde: '.$shields.'/'.$maxshields);
	}
	return $bar;
}
function getStatusBar($color,$amount,$title='') {
	return '<img src="assets/bars/balken.png" style="background-color: #'.$color.';height: 12px; width:'.round($amount).'px;" title="'.$title.'" />';
}

function getPercentageStatusBar($color,$amount,$maxamount) {
	$pro = getPercentage($amount,$maxamount);
	$bar = getStatusBar($color,ceil($pro/2),'Status: '.$pro.'%');
	if ($pro < 100) {
		$bar .= getStatusBar(STATUSBAR_GREY,floor((100-$pro)/2),'Status: '.$pro.'%');
	}
	return $bar;
}
function getPercentage($val,$maxval) {
	if ($val > $maxval) {
	       $val = $maxval;
	}
	return max(0,@round((100/$maxval)*min($val,$maxval)));
}
function renderHuellStatusBar(&$huell,&$maxhuell) {
	$pro = getPercentage($huell,$maxhuell);
	$bar = getStatusBar(STATUSBAR_GREEN,ceil($pro/2),'Hülle: '.$huell.'/'.$maxhuell);
	if ($pro < 100) {
		$bar .= getStatusBar(STATUSBAR_GREY,floor((100-$pro)/2),'Hülle: '.$huell.'/'.$maxhuell);
	}
	return $bar;
}
function renderStorageStatusBar(&$storage,&$maxstorage) {
	$pro = getPercentage($storage,$maxstorage);
	$bar = getStatusBar(STATUSBAR_GREEN,ceil($pro/2),'Lager: '.$storage.'/'.$maxstorage.' ('.$pro.'%)');
	if ($pro < 100) {
		$bar .= getStatusBar(STATUSBAR_GREY,floor((100-$pro)/2),'Lager: '.$storage.'/'.$maxstorage.' ('.$pro.'%)');
	}
	return $bar;
}
function renderEpsStatusBar(&$eps,&$maxeps) {
	$pro = getPercentage($eps,$maxeps);
	$bar = getStatusBar(STATUSBAR_YELLOW,ceil($pro/2),'Energie: '.$eps.'/'.$maxeps);
	if ($pro < 100) {
		$bar .= getStatusBar(STATUSBAR_GREY,floor((100-$pro)/2),'Energie: '.$eps.'/'.$maxeps);
	}
	return $bar;
}
function bbHandleColor($action, $attributes, $content, $params, &$node_object) {
        if (!isset ($attributes['default'])) {
		return "";
	}
	$color = str_replace(array('&quot;','"'),'',stripslashes($attributes['default']));
        return "<span style=\"color: ".$color."\">".$content."</span>";
}
function getOnlineStatus($online) {
	if ($online) {
		return "online";
	}
	return "offline";
}
function generatePassword($length=6) {
 
	$dummy = array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'), array('#','&','@','$','_','%','?','+'));
 
	mt_srand((double)microtime()*1000000);

	for ($i = 1; $i <= (count($dummy)*2); $i++) {
		$swap = mt_rand(0,count($dummy)-1);
		$tmp = $dummy[$swap];
		$dummy[$swap] = $dummy[0];
		$dummy[0] = $tmp;
	}
 
	return substr(implode('',$dummy),0,$length);
}
function formatSeconds($time) {
	$h = floor($time / 3600);
	$time -= $h * 3600;
	$m = floor($time / 60);
	$time -= $m * 60;

	$ret = '';
	if ($h > 0) {
		$ret .= $h.'h';
	}
	if ($m > 0) {
		$ret .= ' '.$m.'m';
	}
	if ($time > 0) {
		$ret .= ' '.$time.'s';
	}
	return $ret;
}
function parseDateTime($value) {
	return date("d.m.Y H:i",$value);
}
function parseDate($value) {
	return date("d.m.Y",$value);
}
function infoToString(&$info) {
	return implode("\n",$info);
}
function databaseScan($database_id,$user_id) {
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

	return sprintf(_("Neuer Datenbankeintrag: %s (+%d Punkte)"),$entry->getDescription(),$entry->getCategory()->getPoints());
}
/**
 */
function calculateModuleValue($rump,$module,$callback='aggi',$value=FALSE) { #{{{
	if (!$value) {
		$value = $rump->$callback();
	}
	if ($rump->getModuleLevel() > $module->getLevel()) {
		return round($value-$value/100*$module->getDowngradeFactor());
	}
	if ($rump->getModuleLevel() < $module->getLevel()) {
		return round($value+$value/100*$module->getUpgradeFactor());
	}
	return $value;
} # }}}

/**
 */
function calculateDamageImpact(ShipRumpData $rump, ModulesData $module) { #{{{
	if ($rump->getModuleLevel() > $module->getLevel()) {
		return '-'.$module->getDowngradeFactor().'%';
	}
	if ($rump->getModuleLevel() < $module->getLevel()) {
		return '+'.$module->getUpgradeFactor().'%';
	}
	return _('Normal');
} # }}}

/**
 */
function calculateEvadeChance($rump,$module) { #{{{
	$base = $rump->getEvadeChance();
	if ($rump->getModuleLevel() > $module->getLevel()) {
		$value = (1-$base/100) * 1/(1 - $module->getDowngradeFactor()/100);
	} elseif ($rump->getModuleLevel() < $module->getLevel()) {
		$value = (1-$base/100) * 1/(1 + $module->getUpgradeFactor()/100);
	} else {
		return $base;
	}
	return round((1-$value)*100);
} # }}}

function printBackTrace() {
        echo '<div style="background-color:rgb(240,240,240)">';
        foreach (debug_backtrace() as $bt) {
                printf('<pre>%s%s%s(%s) </pre>'
                .'<blockquote>%s:%d</blockquote>',
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
function getContactlistModes() { #{{{
	return array(ContactlistData::CONTACT_FRIEND => array("mode" => ContactlistData::CONTACT_FRIEND,"name" => _("Freund")),
		     ContactlistData::CONTACT_ENEMY => array("mode" => ContactlistData::CONTACT_ENEMY,"name" => _("Feind")),
		     ContactlistData::CONTACT_NEUTRAL => array("mode" => ContactlistData::CONTACT_NEUTRAL,"name" => _("Neutral")));
} # }}}
/**
 */
function getDefaultTechs() { #{{{
	return array(RESEARCH_START_FEDERATION,RESEARCH_START_ROMULAN,RESEARCH_START_KLINGON,RESEARCH_START_CARDASSIAN,RESEARCH_START_FERENGI,RESEARCH_START_EMPIRE);
} # }}}
/**
 */
function isSystemUser($userId) { #{{{
	$user = array(USER_NOONE);
	return in_array($userId,$user);
} # }}}

/**
 */
function getModuleLevelClass(ShipRumpData $rump,$module) { #{{{
	if ($rump->getModuleLevels()->{'getModuleLevel'.$module->getModule()->getType()}() > $module->getModule()->getLevel()) {
		return 'module_positive';
	}
	if ($rump->getModuleLevels()->{'getModuleLevel'.$module->getModule()->getType()}() < $module->getModule()->getLevel()) {
		return 'module_negative';
	}
	return '';
} # }}}

/**
 *
 */
function jsquote($str) { #{{{
        return str_replace(
                array(
                        "\\",
                        "'",
                     ),
                array(
                        "\\\\",
                        "\\'",
                     ),
                $str
                );
} #}}}

function &DB() {
    static $DB = NULL;
    if ($DB === NULL) {
        global $container;
        return $container->get(DbInterface::class);
    }
    return $DB;
}

function &getMapType(&$type) {
    static $mapTypes = array();
    if (!array_key_exists($type,$mapTypes)) {
        $mapTypes[$type] = new MapFieldType($type);
    }
    return $mapTypes[$type];
}

function currentUser(): User {
	static $currentUser = NULL;
	if ($currentUser === NULL) {
		global $_SESSION;
		$currentUser = User::getById($_SESSION['uid']);
	}
	return $currentUser;
}
function BBCode(): Parser {
	global $container;

	return $container->get(Parser::class);
}

TalesRegistry::registerPrefix(
    'isPositive',
    function($src, $nothrow): string {
        return '(int) '.TalesInternal::compileToPHPExpression($src,$nothrow).' > 0';
    }
);
TalesRegistry::registerPrefix(
    'isNegative',
    function($src, $nothrow): string {
        return '(int) '.TalesInternal::compileToPHPExpression($src,$nothrow).' < 0';
    }
);

TalesRegistry::registerPrefix(
    'bbcode',
    function($src, $nothrow): string {
        return 'BBCode()->parse('.TalesInternal::compileToPHPExpression($src,$nothrow).')->getAsHtml()';
    }
);
TalesRegistry::registerPrefix(
    'bbcode2txt',
    function($src, $nothrow): string {
        return 'BBCode()->parse('.TalesInternal::compileToPHPExpression($src,$nothrow).')->getAsText()';
    }
);
TalesRegistry::registerPrefix(
    'jsquote',
    function ($src, $nothrow): string {
        return 'jsquote('.TalesInternal::compileToPHPExpression($src,$nothrow).')';
    }
);
TalesRegistry::registerPrefix(
    'datetime',
    function ($src, $nothrow): string {
        return 'date(\'d.m.Y H:i\', '.TalesInternal::compileToPHPExpression($src,$nothrow).')';
    }
);
TalesRegistry::registerPrefix(
    'date',
    function ($src, $nothrow): string {
        return 'date(\'d.m.Y\', '.TalesInternal::compileToPHPExpression($src,$nothrow).')';
    }
);
TalesRegistry::registerPrefix(
    'nl2br',
    function ($src, $nothrow): string {
        return 'nl2br('.TalesInternal::compileToPHPExpression($src,$nothrow).')';
    }
);
