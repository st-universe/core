<?php

use Stu\Module\Colony\Lib\PlanetGenerator\PlanetGenerator;

$data[PlanetGenerator::COLGEN_DETAILS] = "Klasse X - Vulkanisch";

$data[PlanetGenerator::CONFIG_COLGEN_SIZEW] = 10;
$data[PlanetGenerator::CONFIG_COLGEN_SIZEH] = 6;

$hasGround = 1;
$hasOrbit = 1;

$data[PlanetGenerator::COLGEN_BASEFIELD] = 918;
$odata[PlanetGenerator::COLGEN_BASEFIELD] = 900;
$udata[PlanetGenerator::COLGEN_BASEFIELD] = 828;

$phases = 0;
$ophases = 0;
$uphases = 0;

$vulkan = rand(10, 13);
$magma = rand(5, 9);

// Surface Phases



$phase[$phases][PlanetGenerator::COLGEN_MODE] = "equatorial";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Lava rechtsrunter";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "918");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "909");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Lavastrom horizontal";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "918");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "910");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(909);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Vulkan links";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "918");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "906");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(910);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Lava Ende";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "918");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "922");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(909);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "polar seeding south";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Vulkan rechts";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "918");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "904");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 2;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Lava Ende";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "918");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "914");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(904);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "polar seeding north";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Vulkan runter";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "918");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "919");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 2;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Lava runter";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "918");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "907");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(919);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Lava Ende";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "918");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "923");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(907);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "equatorial";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Lava Ende";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "918");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "912");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 25;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Lava links runter";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "918");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "911");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(912);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Vulkan hoch";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "918");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "920");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(911);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
$phases++;



$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Plattform";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "918");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "916");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 10;
$phases++;


$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Vulkan erloschen";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $vulkan;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "918");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "902");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = array("0" => "902");
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 30;
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
$uphase[$uphases][PlanetGenerator::COLGEN_FRAGMENTATION] = 8;
$uphases++;



return [
    $odata,
    $data,
    $udata,
    $ophase,
    $phase,
    $uphase,
    $hasGround, $hasOrbit
];
