<?php

use Stu\PlanetGenerator\PlanetGenerator;

$data[PlanetGenerator::COLGEN_DETAILS] = "Klasse X";

$data[PlanetGenerator::COLGEN_SIZEW] = 10;
$data[PlanetGenerator::COLGEN_SIZEH] = 6;

$hasground = 1;

$data[PlanetGenerator::COLGEN_BASEFIELD] = 918;
$odata[PlanetGenerator::COLGEN_BASEFIELD] = 900;
$udata[PlanetGenerator::COLGEN_BASEFIELD] = 828;

$phases = 0;
$ophases = 0;
$uphases = 0;

$vulkan = rand(5, 7);
$magma = rand(5, 9);

// Surface Phases


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Plattform";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "918");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "916");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = ;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 10;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Vulkan erloschen";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $vulkan;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "918");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "902");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 20;
$phases++;


// Underground

$uphase[$uphases][PlanetGenerator::COLGEN_MODE] = "normal";
$uphase[$uphases][PlanetGenerator::COLGEN_DESCRIPTION] = "Untergrund Magma";
$uphase[$uphases][PlanetGenerator::COLGEN_NUM] = $magma;
$uphase[$uphases][PlanetGenerator::COLGEN_FROM] = array("0" => "828");
$uphase[$uphases][PlanetGenerator::COLGEN_TO] = array("0" => "831");
$uphase[$uphases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_FRAGMENTATION] = 2;
$uphases++;



return [
    $odata,
    $data,
    $udata,
    $ophase,
    $phase,
    $uphase,
    $ophases,
    $phases,
    $uphases,
    $hasground
];
