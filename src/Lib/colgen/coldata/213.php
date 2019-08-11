<?php
	$data[details] = "Klasse H - Basisklasse Wüste";

	$bonusdata = array(BONUS_AENER,BONUS_AENER,BONUS_HABI);
	
	$data[sizew] = 10;
	$data[sizeh] = 6;

	$hasground = 1;
	
	$data[basefield] = 401;
	$odata[basefield] = 900;
	$udata[basefield] = 802;

	$phases = 0;
	$ophases = 0;
	$uphases = 0;
		

	
	
	// config
	
	$felsen = rand(30,40);
	$berge  = rand(13,15);
	$dünen  = rand(17,20);
	
	$erde   = rand(6,10);
	
	
	
	
	
	// Surface Phases
	
	$phase[$phases][mode] = "normal";
	$phase[$phases][description] = "Fels";
	$phase[$phases][num] = $felsen;
	$phase[$phases][from] = array("0" => "401");
	$phase[$phases][to]   = array("0" => "713");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 10;	
	$phases++;

	$phase[$phases][mode] = "normal";
	$phase[$phases][description] = "Berge";
	$phase[$phases][num] = $berge;
	$phase[$phases][from] = array("0" => "713");
	$phase[$phases][to]   = array("0" => "703");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 5;	
	$phases++;
	
	$phase[$phases][mode] = "nocluster";
	$phase[$phases][description] = "Dünen";
	$phase[$phases][num] = $dünen;
	$phase[$phases][from] = array(401,713);
	$phase[$phases][to]   = array(403,404);
	$phase[$phases][adjacent] = array(401,403,404);
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 50;	
	$phases++;	
	
	
	
	
	
	
	
	$uphase[$uphases][mode] = "normal";
	$uphase[$uphases][description] = "Erde";
	$uphase[$uphases][num] = $erde;
	$uphase[$uphases][from] = array(802);
	$uphase[$uphases][to]   = array(801);
	$uphase[$uphases][adjacent] = 0;
	$uphase[$uphases][noadjacent] = 0;
	$uphase[$uphases][noadjacentlimit] = 0;	
	$uphase[$uphases][fragmentation] = 15;	
	$uphases++;
	
	
	
	
	
?>