<?php

use Stu\Module\Colony\Lib\PlanetGenerator\PlanetGenerator;

$data[PlanetGenerator::COLGEN_DETAILS] = "Klasse Q";

$data[PlanetGenerator::CONFIG_COLGEN_SIZEW] = 7;
$data[PlanetGenerator::CONFIG_COLGEN_SIZEH] = 5;

$hasGround = 0;
$hasOrbit = 1;

$data[PlanetGenerator::COLGEN_BASEFIELD] = 940;
$odata[PlanetGenerator::COLGEN_BASEFIELD] = 900;
$udata[PlanetGenerator::COLGEN_BASEFIELD] = 947;

$phases = 0;
$ophases = 0;
$uphases = 0;

$sturm = rand(4, 8);
$platform = rand(1, 1);

// Surface Phases

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "crater seeding";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Plasmasee 1";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "940");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "942");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Plasmasee 1";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "940");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "943");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(942);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Plasmasee 1";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "940");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "944");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(942);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Plasmasee 1";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "940");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "945");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(944);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "nocluster";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Stürme";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $sturm;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "940");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "941");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = array();
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Platform";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $platform;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "940");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "946");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = array(943, 944, 945, 942);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 5;
$phases++;


// Orbit Phases

$ophase[$ophases][PlanetGenerator::COLGEN_MODE] = "lower orbit";
$ophase[$ophases][PlanetGenerator::COLGEN_DESCRIPTION] = "Lower Orbit";
$ophase[$ophases][PlanetGenerator::COLGEN_NUM] = 7;
$ophase[$ophases][PlanetGenerator::COLGEN_FROM] = array("0" => "900");
$ophase[$ophases][PlanetGenerator::COLGEN_TO] = array("0" => "948");
$ophase[$ophases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$ophase[$ophases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$ophase[$ophases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$ophase[$ophases][PlanetGenerator::COLGEN_FRAGMENTATION] = 2;
$ophases++;

return [
    $odata,
    $data,
    $udata,
    $ophase,
    $phase,
    [],
    $hasGround, $hasOrbit
];
