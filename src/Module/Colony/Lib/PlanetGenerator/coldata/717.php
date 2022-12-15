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
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717010");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(900);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717020");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717010);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717030");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717020);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Gestein";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717040");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717030);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Gestein";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717050");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717040);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717060");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717050);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717070");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717010);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717080");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717070);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717090");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717080);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717100");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717090);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Berge";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717110");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717100);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Gestein";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717120");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717110);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717130");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717070);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Gestein";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717140");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717130);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717150");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717140);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717160");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717150);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717170");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717160);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717180");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717170);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717190");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717130);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Gestein";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717200");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717190);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Berge";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717210");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717200);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717220");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717210);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717230");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717220);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717240");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717230);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717250");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717190);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717260");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717250);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717270");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717260);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717280");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717270);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717290");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717280);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717300");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717290);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717310");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717250);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717320");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717310);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717330");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717320);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717340");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717330);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717350");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717340);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "717360");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(717350);
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