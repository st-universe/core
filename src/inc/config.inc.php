<?php

use Noodlehaus\ConfigInterface;
use Psr\Container\ContainerInterface;

require_once __DIR__.'/../../vendor/autoload.php';

ini_set('date.timezone', 'Europe/Berlin');

$benchmark_start = microtime();


/**
 * @var ContainerInterface $container
 */
$container = require_once __DIR__.'/../Config/bootstrap.php';

require_once __DIR__ . '/../Config/ErrorHandler.php';

/**
 */
function isCommandLineCall() { #{{{
	if (!$_SERVER['SERVER_NAME']) {
		return TRUE;
	}
	return FALSE;
} # }}}

// General config

function getAdminUserIds() {
	return array(101);
}

/**
 */
function isAdmin($user_id) { #{{{
	return in_array($user_id,getAdminUserIds());
} # }}}

$config = $container->get(ConfigInterface::class);

$webrootPath = $config->get('game.webroot');

// Generator dirs
define("GENERATOR_DIR", $webrootPath."/src/admin/generators/");
define("GENERATED_DIR", $webrootPath."/src/inc/generated/");

set_include_path(get_include_path() . PATH_SEPARATOR . $webrootPath);

define('SHIELD_REGENERATION_TIME',900);

define('SHIP_CATEGORY_DEBRISFIELD',7);

//XXX

define("AVATAR_USER_PATH", "/html/avatare/user/");
define("AVATAR_ALLIANCE_PATH", "/html/avatare/alliance/");
define("AVATAR_USER_PATH_INTERNAL", $webrootPath.'/'.AVATAR_USER_PATH);
define("AVATAR_ALLIANCE_PATH_INTERNAL", $webrootPath.'/'.AVATAR_ALLIANCE_PATH);

define("BUILDMENU_SCROLLOFFSET",6);

define('INDICATOR_MAX','m');

// Map
define("MAP_MAX_X",120);
define("MAP_MAX_Y",120);

// Special User
define("USER_NOONE",1);

// Trümmerfeld
define("TRUMFIELD_CLASS",8);

define("USER_ONLINE_PERIOD",300);

// Starmap
define("MAPTYPE_INSERT",1);
define("MAPTYPE_DELETE",2);

define("SYSTEM_ECOST_NBS",1);
define("SYSTEM_ECOST_SHIELDS",1);
define("SYSTEM_ECOST_CLOAK",2);
define("SYSTEM_ECOST_LSS",1);
define("SYSTEM_ECOST_PHASER",1);
define("SYSTEM_ECOST_TORPEDO",1);
define("SYSTEM_ECOST_DOCK",1);
define("SYSTEM_ECOST_TRACTOR",1);
define("SYSTEM_ECOST_WARP",1);

define('WARPCORE_LOAD',20);
define('WARPCORE_CAPACITY_MULTIPLIER',15);

define('POINTS_PER_FLEET',60);

// Trade
define("MAX_TRADELICENCE_COUNT",6);

// Modules
define("MODULE_TYPE_COUNT",9);

// Base Techs
define('RESEARCH_START_FEDERATION',1001);
define('RESEARCH_START_ROMULAN',1002);
define('RESEARCH_START_KLINGON',1003);
define('RESEARCH_START_CARDASSIAN',1004);
define('RESEARCH_START_FERENGI',1005);
define('RESEARCH_START_EMPIRE',1006);

// Maindesk link
define('MAINDESK', '/maindesk.php');
define("CONFIG_GAMESTATE",1);

define("CONFIG_GAMESTATE_VALUE_ONLINE",1);
define("CONFIG_GAMESTATE_VALUE_TICK",2);
define("CONFIG_GAMESTATE_VALUE_MAINTENANCE",3);


define('ALLIANCE_JOBS_FOUNDER',1);
define('ALLIANCE_JOBS_SUCCESSOR',2);
define('ALLIANCE_JOBS_DIPLOMATIC',3);
define('ALLIANCE_JOBS_PENDING',4);

define("ALLIANCE_RELATION_WAR",1);
define("ALLIANCE_RELATION_PEACE",2);
define("ALLIANCE_RELATION_FRIENDS",3);
define("ALLIANCE_RELATION_ALLIED",4);

define("BUILDING_FUNCTION_CENTRAL",1);
define("BUILDING_FUNCTION_AIRFIELD",4);
define("BUILDING_FUNCTION_FIGHTER_SHIPYARD",5);
define("BUILDING_FUNCTION_ESCORT_SHIPYARD",6);
define("BUILDING_FUNCTION_FRIGATE_SHIPYARD",7);
define("BUILDING_FUNCTION_CRUISER_SHIPYARD",8);
define("BUILDING_FUNCTION_TORPEDO_FAB",9);
define('BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL1',10);
define('BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL2',11);
define('BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL3',12);
define('BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL1',13);
define('BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL2',14);
define('BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL3',15);
define('BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL1',16);
define('BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL2',17);
define('BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL3',18);
define('BUILDING_FUNCTION_ACADEMY',20);
define("BUILDING_FUNCTION_DESTROYER_SHIPYARD",21);
define("BUILDING_FUNCTION_REPAIR_SHIPYARD",22);

define('COLONY_FIELDTYPE_MEADOW',101);

define('COLONY_CLASS_SPECIAL_RING',1);

define("CREW_GENDER_MALE",1);
define("CREW_GENDER_FEMALE",2);

define("CREW_TYPE_COMMAND",1);
define("CREW_TYPE_SECURITY",2);
define("CREW_TYPE_SCIENCE",3);
define("CREW_TYPE_TECHNICAL",4);
define("CREW_TYPE_NAVIGATION",5);
define("CREW_TYPE_CREWMAN",6);
define("CREW_TYPE_CAPTAIN",7);

define("CREW_TYPE_FIRST",CREW_TYPE_COMMAND);
define("CREW_TYPE_LAST",CREW_TYPE_CAPTAIN);

define('DAMAGE_TYPE_PHASER',1);
define('DAMAGE_TYPE_TORPEDO',2);

define('DAMAGE_MODE_HULL',1);
define('DAMAGE_MODE_SHIELDS',2);

define('DATABASE_CATEGORY_SHIPRUMP', 1);
define('DATABASE_CATEGORY_RPGSHIP', 2);
define('DATABASE_CATEGORY_TRADEPOST', 3);
define('DATABASE_CATEGORY_REGION', 4);
define('DATABASE_CATEGORY_PLANET_TYPE', 5);
define('DATABASE_CATEGORY_STAR_SYSTEM_TYPE', 6);
define('DATABASE_CATEGORY_STARSYSTEM', 7);

define("DATABASE_TYPE_SHIPRUMP",1);
define("DATABASE_TYPE_RPGSHIPS",2);
define('DATABASE_TYPE_POI', 3);
define('DATABASE_TYPE_STARSYSTEM', 4);
define('DATABASE_TYPE_STARSYSTEM_TYPE', 5);
define('DATABASE_TYPE_PLANET', 6);
define("DATABASE_TYPE_MAP",7);

define('DOCK_PRIVILEGE_USER',1);
define('DOCK_PRIVILEGE_ALLIANCE',2);
define('DOCK_PRIVILEGE_FACTION',3);

define('DOCK_PRIVILEGE_MODE_ALLOW',1);
define('DOCK_PRIVILEGE_MODE_DENY',2);

define('FACTION_FEDERATION',1);
define('FACTION_ROMULAN',2);
define('FACTION_KLINGON',3);
define('FACTION_CARDASSIAN',4);
define('FACTION_FERENGI',5);
define('FACTION_EMPIRE',6);

define('GOOD_SATISFACTION_FED_PRIMARY',1001);
define('GOOD_SATISFACTION_ROMULAN_PRIMARY',1002);
define('GOOD_SATISFACTION_KLINGON_PRIMARY',1003);

define('GOOD_SATISFACTION_FED_SECONDARY',1601);
define('GOOD_SATISFACTION_ROMULAN_SECONDARY',1602);
define('GOOD_SATISFACTION_KLINGON_SECONDARY',1603);

define('ROLE_PHASERSHIP',1);
define('ROLE_PULSESHIP',2);
define('ROLE_TORPEDOSHIP',3);

define("ALERT_GREEN",1);
define("ALERT_YELLOW",2);
define("ALERT_RED",3);

define('DISABLED_COMPLETE',1);
define('DISABLED_LOST',2);

define('SHIP_STATE_NONE',0);
define('SHIP_STATE_REPAIR',1);

define("SYSTEM_EPS",1);
define("SYSTEM_IMPULSEDRIVE",2);
define("SYSTEM_WARPCORE",3);
define("SYSTEM_COMPUTER",4);
define("SYSTEM_PHASER",5);
define("SYSTEM_TORPEDO",6);
define("SYSTEM_CLOAK",7);
define("SYSTEM_LSS",8);
define("SYSTEM_NBS",9);
define("SYSTEM_WARPDRIVE",10);
define("SYSTEM_SHIELDS",11);

define("MENU_INFO",2);
define("MENU_BUILD",1);
define("MENU_SOCIAL",4);
define("MENU_BUILDINGS",5);
define("MENU_OPTION",3);
define("MENU_AIRFIELD",6);
define("MENU_SHIPYARD",8);
define("MENU_BUILDPLANS",9);
define('MENU_MODULEFAB',7);
define('MENU_FIGHTER_SHIPYARD',10);
define('MENU_TORPEDOFAB',11);
define('MENU_ACADEMY',12);

define("RESEARCH_MODE_EXCLUDE",0);
define("RESEARCH_MODE_REQUIRE",1);
define("RESEARCH_MODE_REQUIRE_SOME",2);

define('FLY_RIGHT',1);
define('FLY_LEFT',2);
define('FLY_UP',3);
define('FLY_DOWN',4);

define("MODULE_TYPE_HULL",1);
define("MODULE_TYPE_SHIELDS",2);
define("MODULE_TYPE_EPS",3);
define("MODULE_TYPE_IMPULSEDRIVE",4);
define("MODULE_TYPE_WARPCORE",5);
define("MODULE_TYPE_COMPUTER",6);
define("MODULE_TYPE_PHASER",7);
define("MODULE_TYPE_TORPEDO",8);
define("MODULE_TYPE_SPECIAL",9);

define('BUILDMENU_SOCIAL',1);
define('BUILDMENU_INDUSTRY',2);
define('BUILDMENU_INFRASTRUCTURE',3);


require_once 'func.inc.php';
include_once("generated/fieldtypesname.inc.php");
