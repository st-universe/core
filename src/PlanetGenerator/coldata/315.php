<?php

use Stu\PlanetGenerator\PlanetGenerator;

$data[PlanetGenerator::COLGEN_DETAILS] = "Klasse P - Basisklasse Eis";

$bonusdata = array(
    PlanetGenerator::BONUS_WENERGY,
    PlanetGenerator::BONUS_WENERGY,
    PlanetGenerator::BONUS_DEUTERIUM,
    PlanetGenerator::BONUS_WATERFOOD,
    PlanetGenerator::BONUS_WATERFOOD
);

$data[PlanetGenerator::CONFIG_COLGEN_SIZEW] = 10;
$data[PlanetGenerator::CONFIG_COLGEN_SIZEH] = 6;

$hasground = 1;

$data[PlanetGenerator::COLGEN_BASEFIELD] = 221;
$odata[PlanetGenerator::COLGEN_BASEFIELD] = 900;
$udata[PlanetGenerator::COLGEN_BASEFIELD] = 802;

$phases = 0;
$ophases = 0;
$uphases = 0;


// config

$land = rand(48, 50);
$berge = rand(10, 13);
$eisf = rand(5, 8);

$uerde = rand(4, 8);
$ueis = rand(4, 5);


// Surface Phases

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Landmassen";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $land;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array(221);
$phase[$phases][PlanetGenerator::COLGEN_TO] = array(501);
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 10;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Berge";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $berge;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "501");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "704");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = array(221);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 15;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "nocluster";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Eisformation";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $eisf;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array(501);
$phase[$phases][PlanetGenerator::COLGEN_TO] = array(511);
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;


// Orbit Phases

$ophase[$ophases][PlanetGenerator::COLGEN_MODE] = "upper orbit";
$ophase[$ophases][PlanetGenerator::COLGEN_DESCRIPTION] = "Lower Orbit";
$ophase[$ophases][PlanetGenerator::COLGEN_NUM] = 10;
$ophase[$ophases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$ophase[$ophases][PlanetGenerator::COLGEN_TO] = array("0" => "915");
$ophase[$ophases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$ophase[$ophases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$ophase[$ophases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$ophase[$ophases][PlanetGenerator::COLGEN_FRAGMENTATION] = 2;
$ophases++;


$uphase[$uphases][PlanetGenerator::COLGEN_MODE] = "normal";
$uphase[$uphases][PlanetGenerator::COLGEN_DESCRIPTION] = "Erde";
$uphase[$uphases][PlanetGenerator::COLGEN_NUM] = $uerde;
$uphase[$uphases][PlanetGenerator::COLGEN_FROM] = array(802);
$uphase[$uphases][PlanetGenerator::COLGEN_TO] = array(801);
$uphase[$uphases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_FRAGMENTATION] = 15;
$uphases++;

$uphase[$uphases][PlanetGenerator::COLGEN_MODE] = "normal";
$uphase[$uphases][PlanetGenerator::COLGEN_DESCRIPTION] = "Eis";
$uphase[$uphases][PlanetGenerator::COLGEN_NUM] = $ueis;
$uphase[$uphases][PlanetGenerator::COLGEN_FROM] = array(802);
$uphase[$uphases][PlanetGenerator::COLGEN_TO] = array(821);
$uphase[$uphases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_FRAGMENTATION] = 25;
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
