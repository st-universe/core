<?php

use Stu\Lib\PlanetGenerator\PlanetGenerator;

$data[PlanetGenerator::COLGEN_DETAILS] = "Klasse I/J";

$data[PlanetGenerator::COLGEN_SIZEW] = 10;
$data[PlanetGenerator::COLGEN_SIZEH] = 6;

$hasground = 0;
$bonusfields = 0;


$data[PlanetGenerator::COLGEN_BASEFIELD] = 201;
$odata[PlanetGenerator::COLGEN_BASEFIELD] = 900;
$udata[PlanetGenerator::COLGEN_BASEFIELD] = 802;

$phases = 0;
$ophases = 0;
$uphases = 0;

// Surface Phases

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "fullsurface";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Komplett-Oberfläche";
$phase[$phases][PlanetGenerator::COLGEN_TYPE] = 63;
$phases++;

return [
    $odata,
    $data,
    $udata,
    $ophase,
    [],
    [],
    $ophases,
    $phases,
    $uphases,
    $hasground
];
