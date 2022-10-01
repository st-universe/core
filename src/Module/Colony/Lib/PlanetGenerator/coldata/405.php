<?php

use Stu\Module\Colony\Lib\PlanetGenerator\PlanetGenerator;

$data[PlanetGenerator::COLGEN_DETAILS] = "Klasse O - Basisklasse Ozean";

$bonusdata = array(
    PlanetGenerator::BONUS_WENERGY,
    PlanetGenerator::BONUS_WENERGY,
    PlanetGenerator::BONUS_WATERFOOD,
    PlanetGenerator::BONUS_HABITAT
);

$data[PlanetGenerator::CONFIG_COLGEN_SIZEW] = 7;
$data[PlanetGenerator::CONFIG_COLGEN_SIZEH] = 5;

$hasground = 0;

$data[PlanetGenerator::COLGEN_BASEFIELD] = 201;
$odata[PlanetGenerator::COLGEN_BASEFIELD] = 900;
$udata[PlanetGenerator::COLGEN_BASEFIELD] = 801;

$phases = 0;
$ophases = 0;
$uphases = 0;


// config

$land = rand(17, 20);
$berge = rand(3, 5);
$korall = rand(2, 3);
$seicht = rand(5, 7);
$trees = rand(7, 9);


$ufels = rand(4, 7);
$uwasser = 5;


// Surface Phases

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "equatorial";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Korallen";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $korall;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "201");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "211");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 25;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Landmassen";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $land;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "201");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "101");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 100;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "forced adjacency";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Seichtes Wasser";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $seicht;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "201");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "210");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(101, 210);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 200;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Berge";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $berge;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "101");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "701");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 10;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Bäume";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $trees;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "101");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "111");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = array("0" => "401");
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 12;
$phases++;


// Orbit Phases
/*
$ophase[$ophases][ColonyGenerator::COLGEN_MODE] = "lower orbit";
$ophase[$ophases][ColonyGenerator::COLGEN_DESCRIPTION] = "Lower Orbit";
$ophase[$ophases][ColonyGenerator::COLGEN_NUM] = 10;
$ophase[$ophases][ColonyGenerator::COLGEN_FROM] = array("0" => "100");
$ophase[$ophases][ColonyGenerator::COLGEN_TO]   = array("0" => "120");
$ophase[$ophases][ColonyGenerator::COLGEN_ADJACENT] = 0;
$ophase[$ophases][ColonyGenerator::COLGEN_NOADJACENT] = 0;
$ophase[$ophases][ColonyGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$ophase[$ophases][ColonyGenerator::COLGEN_FRAGMENTATION] = 2;
$ophases++;
*/

// Underground Phases

$uphase[$uphases][PlanetGenerator::COLGEN_MODE] = "normal";
$uphase[$uphases][PlanetGenerator::COLGEN_DESCRIPTION] = "Untergrundwasser";
$uphase[$uphases][PlanetGenerator::COLGEN_NUM] = $uwasser;
$uphase[$uphases][PlanetGenerator::COLGEN_FROM] = array("0" => "801");
$uphase[$uphases][PlanetGenerator::COLGEN_TO] = array("0" => "851");
$uphase[$uphases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_FRAGMENTATION] = 2;
$uphases++;

$uphase[$uphases][PlanetGenerator::COLGEN_MODE] = "normal";
$uphase[$uphases][PlanetGenerator::COLGEN_DESCRIPTION] = "Untergrundfels";
$uphase[$uphases][PlanetGenerator::COLGEN_NUM] = $ufels;
$uphase[$uphases][PlanetGenerator::COLGEN_FROM] = array("0" => "801");
$uphase[$uphases][PlanetGenerator::COLGEN_TO] = array("0" => "802");
$uphase[$uphases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_FRAGMENTATION] = 10;
$uphases++;

return [
    $odata,
    $data,
    $udata,
    [],
    $phase,
    $uphase,
    $hasground
];
