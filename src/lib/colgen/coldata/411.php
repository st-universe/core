<?php
	$data[details] = "Klasse H - Basisklasse Ödland";

	$bonusdata = array(BONUS_AENER,BONUS_HABI,BONUS_HABI);
	
	$data[sizew] = 7;
	$data[sizeh] = 5;

	$hasground = 0;
	
	$data[basefield] = 601;
	$odata[basefield] = 900;
	$udata[basefield] = 801;

	$phases = 0;
	$ophases = 0;
	$uphases = 0;
		

	
	// config
	
	$eisn    = rand(1,3);
	$eiss    = (4-$eisn);
	
	$berge   = rand(7,8);
	$wüste   = rand(4,6);
	$felsf   = rand(4,5);
	
	
	$ufels   = rand(4,7);
	$ueis = 5;
	
	
	// Surface Phases
	
	$phase[$phases][mode] = "polar seeding north";
	$phase[$phases][description] = "Polkappe N";
	$phase[$phases][num] = $eisn;
	$phase[$phases][from] = array("0" => "601");
	$phase[$phases][to]   = array("0" => "501");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 2;	
	$phases++;

	$phase[$phases][mode] = "polar seeding south";
	$phase[$phases][description] = "Polkappe S";
	$phase[$phases][num] = $eiss;
	$phase[$phases][from] = array("0" => "601");
	$phase[$phases][to]   = array("0" => "501");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 2;	
	$phases++;

	$phase[$phases][mode] = "equatorial";
	$phase[$phases][description] = "Wüsten";
	$phase[$phases][num] = $wüste;
	$phase[$phases][from] = array("0" => "601");
	$phase[$phases][to]   = array("0" => "402");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = array("0" => "201");
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 25;	
	$phases++;

	$phase[$phases][mode] = "normal";
	$phase[$phases][description] = "Berge";
	$phase[$phases][num] = $berge;
	$phase[$phases][from] = array("0" => "601");
	$phase[$phases][to]   = array("0" => "702");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 20;	
	$phases++;

	$phase[$phases][mode] = "nocluster";
	$phase[$phases][description] = "Felsformation";
	$phase[$phases][num] = $felsf;
	$phase[$phases][from] = array("0" => "601");
	$phase[$phases][to]   = array("0" => "611");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = array();
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 0;	
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
	$uphase[$uphases][description] = "Untergrundeis";
	$uphase[$uphases][num] = $ueis;
	$uphase[$uphases][from] = array("0" => "801");
	$uphase[$uphases][to]   = array("0" => "821");
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