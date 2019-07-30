<?php
	
	function getSystemType()
	{
		$sys = draw(array(1 => 30, 2 => 20, 3 => 20, 4 => 20, 5 => 10));
	
		$system[1] = 900;
		$system[2] = 901;
		$system[3] = 900900;
		$system[4] = 900901;
		$system[5] = 901901;
				
		return $system[$sys];
	}
	
	
?>