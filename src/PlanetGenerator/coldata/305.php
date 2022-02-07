<?php

use Stu\PlanetGenerator\PlanetGenerator;

$data[PlanetGenerator::COLGEN_DETAILS] = "Klasse O - Basisklasse Ozean";

$bonusdata = array(
    PlanetGenerator::BONUS_WENERGY,
    PlanetGenerator::BONUS_WENERGY,
    PlanetGenerator::BONUS_WATERFOOD,
    PlanetGenerator::BONUS_HABITAT
);

$data[PlanetGenerator::CONFIG_COLGEN_SIZEW] = 10;
$data[PlanetGenerator::CONFIG_COLGEN_SIZEH] = 6;

$hasground = 1;

$data[PlanetGenerator::COLGEN_BASEFIELD] = 201;
$odata[PlanetGenerator::COLGEN_BASEFIELD] = 900;
$udata[PlanetGenerator::COLGEN_BASEFIELD] = 801;

$phases = 0;
$ophases = 0;
$uphases = 0;


// config

$land = rand(27, 32);
$berge = rand(5, 6);
$korall = rand(4, 6);
$seicht = rand(9, 13);
$trees = rand(10, 13);


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

$ophase[$ophases][PlanetGenerator::COLGEN_MODE] = "upper orbit";
$ophase[$ophases][PlanetGenerator::COLGEN_DESCRIPTION] = "Lower Orbit";
$ophase[$ophases][PlanetGenerator::COLGEN_NUM] = 10;
$ophase[$ophases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$ophase[$ophases][PlanetGenerator::COLGEN_TO] = array("0" => "905");
$ophase[$ophases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$ophase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$ophase[$ophases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$ophase[$ophases][PlanetGenerator::COLGEN_FRAGMENTATION] = 2;
$ophases++;

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
    $ophase,
    $phase,
    $uphase,
    $hasground
];
