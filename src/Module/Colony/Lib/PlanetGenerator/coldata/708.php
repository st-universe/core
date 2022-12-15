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
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708010");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(900);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708020");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708010);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708030");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708020);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Gestein";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708040");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708030);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Gestein";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708050");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708040);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708060");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708050);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708070");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708010);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708080");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708070);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708090");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708080);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708100");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708090);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Berge";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708110");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708100);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Gestein";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708120");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708110);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708130");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708070);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Gestein";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708140");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708130);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708150");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708140);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708160");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708150);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708170");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708160);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708180");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708170);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708190");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708130);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Gestein";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708200");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708190);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Berge";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708210");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708200);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708220");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708210);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708230");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708220);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708240");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708230);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708250");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708190);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708260");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708250);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708270");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708260);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708280");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708270);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708290");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708280);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708300");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708290);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708310");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708250);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708320");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708310);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708330");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708320);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708340");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708330);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708350");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708340);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Weltraum";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "708360");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(708350);
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