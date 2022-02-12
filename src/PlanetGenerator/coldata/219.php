<?php

use Stu\PlanetGenerator\PlanetGenerator;

$data[PlanetGenerator::COLGEN_DETAILS] = "Klasse G - Basisklasse Tundra";

$bonusdata = array(PlanetGenerator::BONUS_AENERGY, PlanetGenerator::BONUS_HABITAT, PlanetGenerator::BONUS_HABITAT);


$data[PlanetGenerator::CONFIG_COLGEN_SIZEW] = 10;
$data[PlanetGenerator::CONFIG_COLGEN_SIZEH] = 6;

$hasground = 1;

$data[PlanetGenerator::COLGEN_BASEFIELD] = 406;
$odata[PlanetGenerator::COLGEN_BASEFIELD] = 900;
$udata[PlanetGenerator::COLGEN_BASEFIELD] = 806;

$phases = 0;
$ophases = 0;
$uphases = 0;


// config

$eisn = rand(3, 4);
$eiss = ($eisn == 4 ? 3 : rand(3, 4));

$berge = rand(12, 14);
$swamp = rand(3, 5);
$felsf = rand(2, 4);
$jungle = rand(8, 12);

$ufels = rand(4, 7);
$uwasser = 5;



// Surface Phases

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "polar seeding north";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Polkappe N";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $eisn;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "406");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "501");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 2;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "polar seeding south";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Polkappe S";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $eiss;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "406");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "501");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 2;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "equatorial";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Sümpfe";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $swamp;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "406");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "126");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 25;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Berge";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $berge;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "406");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "706");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 20;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Jungle";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $jungle;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "406");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "116");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 20;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "nocluster";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Gestein";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $felsf;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "406");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "716");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = array();
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;


$ophase = [];
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
$uphase[$uphases][PlanetGenerator::COLGEN_FROM] = array("0" => "806");
$uphase[$uphases][PlanetGenerator::COLGEN_TO] = array("0" => "851");
$uphase[$uphases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_FRAGMENTATION] = 2;
$uphases++;

$uphase[$uphases][PlanetGenerator::COLGEN_MODE] = "normal";
$uphase[$uphases][PlanetGenerator::COLGEN_DESCRIPTION] = "Untergrundfels";
$uphase[$uphases][PlanetGenerator::COLGEN_NUM] = $ufels;
$uphase[$uphases][PlanetGenerator::COLGEN_FROM] = array("0" => "806");
$uphase[$uphases][PlanetGenerator::COLGEN_TO] = array("0" => "816");
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
