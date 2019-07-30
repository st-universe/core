<?php

	$moons = draw(array(0 => 30, 1 => 40, 2 => 20, 3 => 10));
	$moonradius = 2;

	$mphases = 0;
	
	$mphase[$mphases][mode] = "nocluster";
	$mphase[$mphases][description] = "Mond base";
	$mphase[$mphases][num] = randround(8 * $mooncount/12);
	$mphase[$mphases][from] = array("0" => "60");
	$mphase[$mphases][to]   = array("0" => "430");
	$mphase[$mphases][adjacent] = 0;
	$mphase[$mphases][noadjacent] = 0;
	$mphase[$mphases][noadjacentlimit] = 0;	
	$mphase[$mphases][fragmentation] = 0;				
	$mphases++;
	
	$mphase[$mphases][mode] = "nocluster";
	$mphase[$mphases][description] = "Mond add";
	$mphase[$mphases][num] = randround(4 * $mooncount/12);
	$mphase[$mphases][from] = array("0" => "60");
	$mphase[$mphases][to]   = array("0" => "403");
	$mphase[$mphases][adjacent] = 0;
	$mphase[$mphases][noadjacent] = 0;
	$mphase[$mphases][noadjacentlimit] = 0;	
	$mphase[$mphases][fragmentation] = 0;				
	$mphases++;
	

	
?>