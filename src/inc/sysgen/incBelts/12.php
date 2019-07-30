<?php

	$bphases = 0;

	$thin = 711;
	$thick = 712;
		
	$density = 120;
	$degradation = 2;
	$fragmentation = 5;
	$invert = rand(0,1);
	$isthick = 1;
		
	$bphase[$bphases][mode] = "normal";
	$bphase[$bphases][description] = "Asteroid inner";
	$bphase[$bphases][num] = 0;
	$bphase[$bphases][from] = 0;
	$bphase[$bphases][to]   = 0;
	$bphase[$bphases][adjacent] = 0;
	$bphase[$bphases][noadjacent] = 0;
	$bphase[$bphases][noadjacentlimit] = 0;	
	$bphase[$bphases][fragmentation] = 20;				
	$bphases++;
			
	$bphase[$bphases][mode] = "normal";
	$bphase[$bphases][description] = "Asteroid inner";
	$bphase[$bphases][num] = 0;
	$bphase[$bphases][from] = 0;
	$bphase[$bphases][to]   = 0;
	$bphase[$bphases][adjacent] = 0;
	$bphase[$bphases][noadjacent] = 0;
	$bphase[$bphases][noadjacentlimit] = 0;	
	$bphase[$bphases][fragmentation] = 20;				
	$bphases++;
		
?>