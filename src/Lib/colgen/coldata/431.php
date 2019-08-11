<?php
	$data[details] = "Klasse D - Basisklasse Fels";

	$bonusdata = array(BONUS_ORE, BONUS_HABI, BONUS_HABI);
	
	$data[sizew] = 7;
	$data[sizeh] = 5;

	$hasground = 0;
	
	$data[basefield] = 201;
	$odata[basefield] = 900;
	$udata[basefield] = 802;

	$phases = 0;
	$ophases = 0;
	$uphases = 0;

	$crater = rand(4,6);
	$berge  = rand(5,6);
	
	// Surface Phases
	
	$phase[$phases][mode] = "crater seeding";
	$phase[$phases][description] = "Krater 1";
	$phase[$phases][num] = 1;
	$phase[$phases][from] = array("0" => "201");
	$phase[$phases][to]   = array("0" => "731");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 0;	
	$phases++;

	
	$phase[$phases][mode] = "right";
	$phase[$phases][description] = "Krater 1";
	$phase[$phases][num] = 1;
	$phase[$phases][from] = array("0" => "201");
	$phase[$phases][to]   = array("0" => "732");
	$phase[$phases][adjacent] = array(731);
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 1;	
	$phases++;	
	
	$phase[$phases][mode] = "below";
	$phase[$phases][description] = "Krater 1";
	$phase[$phases][num] = 1;
	$phase[$phases][from] = array("0" => "201");
	$phase[$phases][to]   = array("0" => "733");
	$phase[$phases][adjacent] = array(731);
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 1;	
	$phases++;		
	
	$phase[$phases][mode] = "right";
	$phase[$phases][description] = "Krater 1";
	$phase[$phases][num] = 1;
	$phase[$phases][from] = array("0" => "201");
	$phase[$phases][to]   = array("0" => "734");
	$phase[$phases][adjacent] = array(733);
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 1;	
	$phases++;
	
	
	
	
	
	
	$phase[$phases][mode] = "normal";
	$phase[$phases][description] = "Fels";
	$phase[$phases][num] = 35;
	$phase[$phases][from] = array("0" => "201");
	$phase[$phases][to]   = array("0" => "715");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 0;	
	$phases++;		

	
	$phase[$phases][mode] = "nocluster";
	$phase[$phases][description] = "kleine Krater";
	$phase[$phases][num] = $crater;
	$phase[$phases][from] = array("0" => "715");
	$phase[$phases][to]   = array("0" => "725");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = array();
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 0;	
	$phases++;	
	
	
	$phase[$phases][mode] = "normal";
	$phase[$phases][description] = "Berge";
	$phase[$phases][num] = $berge;
	$phase[$phases][from] = array("0" => "715");
	$phase[$phases][to]   = array("0" => "705");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = array(731,732,733,734);
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 5;	
	$phases++;		
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
?>