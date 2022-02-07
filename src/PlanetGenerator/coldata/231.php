<?php

use Stu\PlanetGenerator\PlanetGenerator;

$data[PlanetGenerator::COLGEN_DETAILS] = "Klasse D - Basisklasse Fels";

$bonusdata = array(PlanetGenerator::BONUS_ORE, PlanetGenerator::BONUS_HABITAT, PlanetGenerator::BONUS_HABITAT);

$data[PlanetGenerator::CONFIG_COLGEN_SIZEW] = 10;
$data[PlanetGenerator::CONFIG_COLGEN_SIZEH] = 6;

$hasground = 1;

$data[PlanetGenerator::COLGEN_BASEFIELD] = 201;
$odata[PlanetGenerator::COLGEN_BASEFIELD] = 900;
$udata[PlanetGenerator::COLGEN_BASEFIELD] = 814;

$phases = 0;
$ophases = 0;
$uphases = 0;

$crater = rand(8, 12);
$berge = rand(9, 11);

// Surface Phases

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "crater seeding";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater 1";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "201");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "731");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater 1";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "201");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "732");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(731);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater 1";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "201");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "733");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(731);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater 1";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "201");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "734");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(733);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "forced rim";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Abstandshalter";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 12;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "201");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "101");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(731, 732, 733, 734);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "forced rim";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Abstandshalter";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 30;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "201");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "401");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(101);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "crater seeding";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater 2";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "201");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "731");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater 2";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array(201, 401);
$phase[$phases][PlanetGenerator::COLGEN_TO] = array(732, 732);
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(731);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater 2";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array(201, 401);
$phase[$phases][PlanetGenerator::COLGEN_TO] = array(733, 733);
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(731);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater 2";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array(201, 401);
$phase[$phases][PlanetGenerator::COLGEN_TO] = array(734, 734);
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(733);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Fels";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 24;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "101");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "715");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Abstandshalter entfernen";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 24;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "401");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "715");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Abstandshalter entfernen";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 60;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "201");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "715");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "nocluster";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "kleine Krater";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $crater;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "715");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "725");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = array();
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Berge";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $berge;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "715");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "705");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = array(731, 732, 733, 734);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 5;
$phases++;

return [
    $odata,
    $data,
    $udata,
    [],
    $phase,
    [],
    $hasground
];
