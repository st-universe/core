<?php

use Stu\PlanetGenerator\PlanetGenerator;

$data[PlanetGenerator::COLGEN_DETAILS] = "Klasse D - Basisklasse Fels";

	$bonusdata = array(PlanetGenerator::BONUS_ORE, PlanetGenerator::BONUS_HABITAT, PlanetGenerator::BONUS_HABITAT);
	
	$data[PlanetGenerator::COLGEN_SIZEW] = 7;
	$data[PlanetGenerator::COLGEN_SIZEH] = 5;

	$hasground = 0;
	
	$data[PlanetGenerator::COLGEN_BASEFIELD] = 201;
	$odata[PlanetGenerator::COLGEN_BASEFIELD] = 900;
	$udata[PlanetGenerator::COLGEN_BASEFIELD] = 802;

	$phases = 0;
	$ophases = 0;
	$uphases = 0;

	$crater = rand(4,6);
	$berge  = rand(5,6);
	
	// Surface Phases
	
	$phase[$phases][PlanetGenerator::COLGEN_MODE] = "crater seeding";
	$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater 1";
	$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
	$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "201");
	$phase[$phases][PlanetGenerator::COLGEN_TO]   = array("0" => "731");
	$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
	$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
	$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
	$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
	$phases++;

	
	$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
	$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater 1";
	$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
	$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "201");
	$phase[$phases][PlanetGenerator::COLGEN_TO]   = array("0" => "732");
	$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(731);
	$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
	$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
	$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
	$phases++;	
	
	$phase[$phases][PlanetGenerator::COLGEN_MODE] = "below";
	$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater 1";
	$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
	$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "201");
	$phase[$phases][PlanetGenerator::COLGEN_TO]   = array("0" => "733");
	$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(731);
	$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
	$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
	$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
	$phases++;		
	
	$phase[$phases][PlanetGenerator::COLGEN_MODE] = "right";
	$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Krater 1";
	$phase[$phases][PlanetGenerator::COLGEN_NUM] = 1;
	$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "201");
	$phase[$phases][PlanetGenerator::COLGEN_TO]   = array("0" => "734");
	$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(733);
	$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
	$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
	$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 1;
	$phases++;
	
	
	
	
	
	
	$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
	$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Fels";
	$phase[$phases][PlanetGenerator::COLGEN_NUM] = 35;
	$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "201");
	$phase[$phases][PlanetGenerator::COLGEN_TO]   = array("0" => "715");
	$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
	$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
	$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
	$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
	$phases++;		

	
	$phase[$phases][PlanetGenerator::COLGEN_MODE] = "nocluster";
	$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "kleine Krater";
	$phase[$phases][PlanetGenerator::COLGEN_NUM] = $crater;
	$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "715");
	$phase[$phases][PlanetGenerator::COLGEN_TO]   = array("0" => "725");
	$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
	$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = array();
	$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
	$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 0;
	$phases++;	
	
	
	$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
	$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Berge";
	$phase[$phases][PlanetGenerator::COLGEN_NUM] = $berge;
	$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "715");
	$phase[$phases][PlanetGenerator::COLGEN_TO]   = array("0" => "705");
	$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
	$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = array(731,732,733,734);
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
    $ophases,
    $phases,
    $uphases,
    $hasground
];
