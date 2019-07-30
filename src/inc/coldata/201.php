<?php
	$data[details] = "Klasse M - Basisklasse Erdähnlich";

	$data[sizew] = 10;
	$data[sizeh] = 6;

	$hasground = 1;
	
	$data[basefield] = 201;
	$odata[basefield] = 900;
	$udata[basefield] = 801;

	$phases = 0;
	$ophases = 0;
	$uphases = 0;
		
	// BONUS OVERRIDE
	
	// config
	
	$eisn    = rand(2,3);
	$eiss    = ($eisn == 3 ? 2 : rand(2,3));
	
	$land    = rand(35,40);
	$berge   = rand(5,8);
	$wüste   = rand(3,4);
	$bäume   = rand(10,13);
	
	
	$ufels   = rand(4,7);
	$uwasser = 5;
	
	
	// Surface Phases
	
	$phase[$phases][mode] = "polar seeding north";
	$phase[$phases][description] = "Polkappe N";
	$phase[$phases][num] = $eisn;
	$phase[$phases][from] = array("0" => "201");
	$phase[$phases][to]   = array("0" => "501");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 2;	
	$phases++;

	$phase[$phases][mode] = "polar seeding south";
	$phase[$phases][description] = "Polkappe S";
	$phase[$phases][num] = $eiss;
	$phase[$phases][from] = array("0" => "201");
	$phase[$phases][to]   = array("0" => "501");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 2;	
	$phases++;

	$phase[$phases][mode] = "normal";
	$phase[$phases][description] = "Landmassen";
	$phase[$phases][num] = $land;
	$phase[$phases][from] = array("0" => "201");
	$phase[$phases][to]   = array("0" => "101");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 8;	
	$phases++;



	$phase[$phases][mode] = "equatorial";
	$phase[$phases][description] = "Wüsten";
	$phase[$phases][num] = $wüste;
	$phase[$phases][from] = array("0" => "101");
	$phase[$phases][to]   = array("0" => "401");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = array("0" => "201");
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 5;	
	$phases++;

	$phase[$phases][mode] = "normal";
	$phase[$phases][description] = "Berge";
	$phase[$phases][num] = $berge;
	$phase[$phases][from] = array("0" => "101");
	$phase[$phases][to]   = array("0" => "701");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = array("0" => "201");
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
	
	$phase[$phases][mode] = "strict polar";
	$phase[$phases][description] = "Nadelwald 1";
	$phase[$phases][num] = 40;
	$phase[$phases][from] = array("0" => "111");
	$phase[$phases][to]   = array("0" => "112");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = array("0" => "401");
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 20;	
	$phases++;
	
	$phase[$phases][mode] = "forced adjacency";
	$phase[$phases][description] = "Nadelwald 2";
	$phase[$phases][num] = 60;
	$phase[$phases][from] = array("0" => "111");
	$phase[$phases][to]   = array("0" => "112");
	$phase[$phases][adjacent] = array("0" => "501");
	$phase[$phases][noadjacent] = array("0" => "401");
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 20;	
	$phases++;
	
	
	// Orbit Phases
	/*
	$ophase[$ophases][mode] = "lower orbit";
	$ophase[$ophases][description] = "Lower Orbit";
	$ophase[$ophases][num] = 10;
	$ophase[$ophases][from] = array("0" => "100");
	$ophase[$ophases][to]   = array("0" => "120");
	$ophase[$ophases][adjacent] = 0;
	$ophase[$phases][noadjacent] = 0;
	$ophase[$ophases][noadjacentlimit] = 0;	
	$ophase[$ophases][fragmentation] = 2;	
	$ophases++;
	*/
	
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