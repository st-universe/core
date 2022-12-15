<?php

use Stu\Module\Colony\Lib\PlanetGenerator\GeneratorModeEnum;
use Stu\Module\Colony\Lib\PlanetGenerator\PlanetGenerator;

$data[PlanetGenerator::COLGEN_DETAILS] = "Klasse Mittlerer Asteroid";

$data[PlanetGenerator::CONFIG_COLGEN_SIZEW] = 6;
$data[PlanetGenerator::CONFIG_COLGEN_SIZEH] = 6;

$hasGround = 0;
$hasOrbit = 0;

$data[PlanetGenerator::COLGEN_BASEFIELD] = 900;
$odata[PlanetGenerator::COLGEN_BASEFIELD] = 900;
$udata[PlanetGenerator::COLGEN_BASEFIELD] = 900;

$phases = 0;
$ophases = 0;
$uphases = 0;

// Surface Phases

$phase[$phases][PlanetGenerator::COLGEN_MODE] = GeneratorModeEnum::TOP_LEFT;
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71701");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(900);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71702");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71701);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71703");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71702);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Gestein";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71704");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71703);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Gestein";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71705");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71704);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71706");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71705);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71707");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71701);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71708");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71707);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71709");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71708);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71710");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71709);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Berge";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71711");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71710);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Gestein";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71712");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71711);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71713");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71707);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Gestein";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71714");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71713);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71715");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71714);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71716");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71715);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71717");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71716);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71718");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71717);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71719");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71713);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Gestein";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71720");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71719);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Berge";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71721");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71720);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71722");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71721);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71723");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71722);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71724");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71723);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71725");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71719);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71726");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71725);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71727");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71726);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71728");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71727);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71729");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71728);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71730");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71729);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71731");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71725);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71732");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71731);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71733");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71732);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71734");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71733);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71735");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71734);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "71736");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(71735);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;


return [
    $odata,
    $data,
    $udata,
    [],
    $phase,
    $uphase,
    $hasGround, $hasOrbit
];