<?php
define('DB_SERVER','172.31.128.2');
define('DB_USER','stu');
define('DB_PASSWORD','stu');
define('DB_DATABASE','stu_db');
$global_path = __DIR__.'/../../';
$loglvl = "7";

define("GAME_VERSION","3.0.0");
define("AVATAR_USER_PATH","html/avatare/user/");
define("AVATAR_ALLIANCE_PATH","html/avatare/alliance/");
define("APP_PATH", $global_path);
define("GFX_PATH","gfx");

define("BUILDMENU_SCROLLOFFSET",5);
define("KNLIMITER",6);
define("PMLIMITER",6);
define("USERLISTLIMITER",25);

// Special PM Categories
define("PM_SPECIAL_MAIN",1);
define("PM_SPECIAL_SHIP",2);
define("PM_SPECIAL_COLONY",3);
define("PM_SPECIAL_TRADE",4);
define("PM_SPECIAL_PMOUT",5);

// Map
define("MAP_MAX_X",500);
define("MAP_MAX_Y",500);

// Special User
define("USER_NOONE",1);

// Trümmerfeld
define("TRUMFIELD_CLASS",8);

define("USER_ONLINE_PERIOD",300);
define("USER_VERIFICATION",TRUE);
define("PEOPLE_FOOD",5);

// Tick
define("LOCKFILE_DIR",'/var/tmp/');
define("PROCESS_COUNT",7);

// Barcolors
define("STATUSBAR_YELLOW",'aaaa00');
define("STATUSBAR_GREEN",'00aa00');
define("STATUSBAR_GREY",'777777');
define("STATUSBAR_RED",'ff0000');
define("STATUSBAR_BLUE",'0070cf');
define("STATUSBAR_DARKBLUE",'004682');

// Generator dirs
define("GENERATOR_DIR",APP_PATH."/admin/generators/");
define("GENERATED_DIR",APP_PATH."/inc/generated/");

// Maintenance
define("MAINTENANCE_DIR",APP_PATH."/admin/maintenance/");

// Backup
define("BACKUP_DIR",APP_PATH."/admin/backup/");
define("BACKUP_PURGE",2592000);

// Starmap
define("MAPFIELDS_PER_SECTION",20);
define("MAPTYPE_INSERT",1);
define("MAPTYPE_DELETE",2);

// Ship
define("SLOT_COUNT",6);
define("SLOT_EMPTY",0);

define("SYSTEM_ECOST_NBS",1);
define("SYSTEM_ECOST_SHIELDS",1);
define("SYSTEM_ECOST_CLOAK",2);
define("SYSTEM_ECOST_LSS",1);
define("SYSTEM_ECOST_PHASER",1);
define("SYSTEM_ECOST_TORPEDO",1);
define("SYSTEM_ECOST_DOCK",1);
define("SYSTEM_ECOST_TRACOTR",1);
define("SYSTEM_ECOST_WARP",1);

// Trade
define("MAX_TRADELICENCE_COUNT",6);

// Modules
define("MODULE_TYPE_COUNT",9);
