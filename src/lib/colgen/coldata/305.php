<?php
	$data[details] = "Klasse O - Basisklasse Ozean";

	$bonusdata = array(BONUS_WENER,BONUS_WENER,BONUS_WFOOD,BONUS_HABI);
	
	$data[sizew] = 10;
	$data[sizeh] = 6;

	$hasground = 1;
	
	$data[basefield] = 201;
	$odata[basefield] = 900;
	$udata[basefield] = 801;

	$phases = 0;
	$ophases = 0;
	$uphases = 0;
		

	// config
	
	$land    = rand(27,32);
	$berge   = rand(5,6);
	$korall  = rand(4,6);
	$seicht  = rand(9,13);
	$bäume   = rand(10,13);
	
	
	$ufels   = rand(4,7);
	$uwasser = 5;
	
	
	// Surface Phases
	
	$phase[$phases][mode] = "equatorial";
	$phase[$phases][description] = "Korallen";
	$phase[$phases][num] = $korall;
	$phase[$phases][from] = array("0" => "201");
	$phase[$phases][to]   = array("0" => "211");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 25;	
	$phases++;
	
	$phase[$phases][mode] = "normal";
	$phase[$phases][description] = "Landmassen";
	$phase[$phases][num] = $land;
	$phase[$phases][from] = array("0" => "201");
	$phase[$phases][to]   = array("0" => "101");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 100;	
	$phases++;

	$phase[$phases][mode] = "forced adjacency";
	$phase[$phases][description] = "Seichtes Wasser";
	$phase[$phases][num] = $seicht;
	$phase[$phases][from] = array("0" => "201");
	$phase[$phases][to]   = array("0" => "210");
	$phase[$phases][adjacent] = array(101,210);
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 200;	
	$phases++;
	
	$phase[$phases][mode] = "normal";
	$phase[$phases][description] = "Berge";
	$phase[$phases][num] = $berge;
	$phase[$phases][from] = array("0" => "101");
	$phase[$phases][to]   = array("0" => "701");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 1;	
	$phase[$phases][fragmentation] = 10;	
	$phases++;
	
	$phase[$phases][mode] = "normal";
	$phase[$phases][description] = "Bäume";
	$phase[$phases][num] = $bäume;
	$phase[$phases][from] = array("0" => "101");
	$phase[$phases][to]   = array("0" => "111");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = array("0" => "401");
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 12;	
	$phases++;
	
	
	// Orbit Phases
	
	$ophase[$ophases][mode] = "upper orbit";
	$ophase[$ophases][description] = "Lower Orbit";
	$ophase[$ophases][num] = 10;
	$ophase[$ophases][from] = array("0" => "900");
	$ophase[$ophases][to]   = array("0" => "905");
	$ophase[$ophases][adjacent] = 0;
	$ophase[$phases][noadjacent] = 0;
	$ophase[$ophases][noadjacentlimit] = 0;	
	$ophase[$ophases][fragmentation] = 2;	
	$ophases++;
	
	// Underground Phases
	
	$uphase[$uphases][mode] = "normal";
	$uphase[$uphases][description] = "Untergrundwasser";
	$uphase[$uphases][num] = $uwasser;
	$uphase[$uphases][from] = array("0" => "801");
	$uphase[$uphases][to]   = array("0" => "851");
	$uphase[$uphases][adjacent] = 0;
	$uphase[$uphases][noadjacent] = 0;
	$uphase[$uphases][noadjacentlimit] = 0;	
	$uphase[$uphases][fragmentation] = 2;	
	$uphases++;
	
	$uphase[$uphases][mode] = "normal";
	$uphase[$uphases][description] = "Untergrundfels";
	$uphase[$uphases][num] = $ufels;
	$uphase[$uphases][from] = array("0" => "801");
	$uphase[$uphases][to]   = array("0" => "802");
	$uphase[$uphases][adjacent] = 0;
	$uphase[$uphases][noadjacent] = 0;
	$uphase[$uphases][noadjacentlimit] = 0;	
	$uphase[$uphases][fragmentation] = 10;	
	$uphases++;	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
?>