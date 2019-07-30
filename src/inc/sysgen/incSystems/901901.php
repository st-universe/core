<?php
	$data[name] = "Oranger Zwerg + Oranger Zwerg";

	$data[radius] = rand(12,14);
	
	$zone[1] = 25;
	$zone[2] = 35;
	$zone[3] = 30;
	$zone[4] = 10;
	
	$stars = 0;

	$star[$stars][type]    =  901;
	$star[$stars][width]   =   2;
	$star[$stars][oborder] =   1;
	$star[$stars][iborder] =   1;
	$stars++;

	$star[$stars][type]    =  901;
	$star[$stars][width]   =   2;
	$star[$stars][oborder] =   1;
	$star[$stars][iborder] =   1;
	$stars++;
	
	$belts = draw(array(0 => 30,1 => 50, 2 => 20));
	if ($belts > 1) $data[radius] += 2;
	
	$belt[1] = draw(array(11 => 25, 12 => 25, 13 => 25, 14 => 25));
	$belt[2] = draw(array(11 => 25, 12 => 25, 13 => 25, 14 => 25));
	
	$data[planets] = $data[radius]-3;			
	if ($belts == 0) $data[planets] += 2;
?>