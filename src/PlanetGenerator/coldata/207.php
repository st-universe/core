<?php

use Stu\PlanetGenerator\PlanetGenerator;

$data[PlanetGenerator::COLGEN_DETAILS] = "Klasse ??";

$data[PlanetGenerator::CONFIG_COLGEN_SIZEW] = 10;
$data[PlanetGenerator::CONFIG_COLGEN_SIZEH] = 6;

$hasground = 1;

$data[PlanetGenerator::COLGEN_BASEFIELD] = 1000;
$odata[PlanetGenerator::COLGEN_BASEFIELD] = 900;
$udata[PlanetGenerator::COLGEN_BASEFIELD] = 802;

$phases = 0;
$ophases = 0;
$uphases = 0;

return [
    $odata,
    $data,
    $udata,
    [],
    [],
    [],
    $hasground
];
