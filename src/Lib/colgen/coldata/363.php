<?php
	$data[details] = "Klasse I/J";

	$data[sizew] = 10;
	$data[sizeh] = 6;

	$hasground = 0;
	$bonusfields = 0;
	
	
	$data[basefield] = 201;
	$odata[basefield] = 900;
	$udata[basefield] = 802;

	$phases = 0;
	$ophases = 0;
	$uphases = 0;
		
	// Surface Phases
		
	$phase[$phases][mode] = "fullsurface";
	$phase[$phases][description] = "Komplett-Oberfläche";
	$phase[$phases][type] = 63;
	$phases++;
	
	// Orbit Phases
	
	$ophase[$ophases][mode] = "upper orbit";
	$ophase[$ophases][description] = "Lower Orbit";
	$ophase[$ophases][num] = 10;
	$ophase[$ophases][from] = array("0" => "900");
	$ophase[$ophases][to]   = array("0" => "963");
	$ophase[$ophases][adjacent] = 0;
	$ophase[$phases][noadjacent] = 0;
	$ophase[$ophases][noadjacentlimit] = 0;	
	$ophase[$ophases][fragmentation] = 2;	
	$ophases++;		
	
	
?>