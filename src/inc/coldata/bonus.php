<?php
	define("ANYFOOD",1);
	define("LANDFOOD",2);
	define("WATERFOOD",3);
	
	define("SWAMP",4);
	
	define("HABITAT",10);
	
	define("ORE",22);
	define("DEUT",21);
	define("ANYRES",20);
	
	define("ENERGY",30);
	
	define("LIMITED",99);

	function gettransformations($mres) {
		$res = array();
		$res[from] = array();
		$res[to] = array();
		if ($mres == ANYFOOD) {
				array_push($res[from],101);
				array_push($res[to],10101);
				array_push($res[from],111);
				array_push($res[to],11101);
				array_push($res[from],112);
				array_push($res[to],11201);
				array_push($res[from],121);
				array_push($res[to],12101);				
				array_push($res[from],201);
				array_push($res[to],20102);		
				array_push($res[from],211);
				array_push($res[to],21102);		
				array_push($res[from],221);
				array_push($res[to],22102);
				array_push($res[from],601);
				array_push($res[to],60101);
				array_push($res[from],611);
				array_push($res[to],61101);
				array_push($res[from],602);
				array_push($res[to],60201);				
			}
			if ($mres == LANDFOOD) {
				array_push($res[from],101);
				array_push($res[to],10101);
				array_push($res[from],111);
				array_push($res[to],11101);
				array_push($res[from],112);
				array_push($res[to],11201);
				array_push($res[from],121);
				array_push($res[to],12101);				
				array_push($res[from],601);
				array_push($res[to],60101);
				array_push($res[from],611);
				array_push($res[to],61101);				
				array_push($res[from],602);
				array_push($res[to],60201);
			}			
			if ($mres == WATERFOOD) {
				array_push($res[from],201);
				array_push($res[to],20102);		
				array_push($res[from],211);
				array_push($res[to],21102);		
				array_push($res[from],221);
				array_push($res[to],22102);		
			}			
		
			if ($mres == HABITAT) {
				array_push($res[from],101);
				array_push($res[to],10103);
				array_push($res[from],111);
				array_push($res[to],11103);
				array_push($res[from],112);
				array_push($res[to],11203);
				array_push($res[from],601);
				array_push($res[to],60103);				
				array_push($res[from],601);
				array_push($res[to],60104);
				array_push($res[from],602);
				array_push($res[to],60203);				
				array_push($res[from],611);
				array_push($res[to],61103);				
				array_push($res[from],611);
				array_push($res[to],61104);
				array_push($res[from],713);
				array_push($res[to],71304);
				array_push($res[from],715);
				array_push($res[to],71504);
				array_push($res[from],725);
				array_push($res[to],72504);
			}		
			if ($mres == ENERGY) {
				array_push($res[from],201);
				array_push($res[to],20132);
				array_push($res[from],221);
				array_push($res[to],22132);
				array_push($res[from],401);
				array_push($res[to],40131);
				array_push($res[from],402);
				array_push($res[to],40231);
				array_push($res[from],403);
				array_push($res[to],40331);
				array_push($res[from],404);
				array_push($res[to],40431);
				array_push($res[from],713);
				array_push($res[to],71331);				
			}			
			if ($mres == DEUT) {
				array_push($res[from],201);
				array_push($res[to],20111);
				array_push($res[from],210);
				array_push($res[to],21011);
				array_push($res[from],211);
				array_push($res[to],21111);
				array_push($res[from],221);
				array_push($res[to],22111);
				array_push($res[from],501);
				array_push($res[to],50111);
				array_push($res[from],511);
				array_push($res[to],51111);				
			}		
			
			if ($mres == ORE) {
				array_push($res[from],701);
				array_push($res[to],70112);
				array_push($res[from],702);
				array_push($res[to],70212);
				array_push($res[from],703);
				array_push($res[to],70312);
				array_push($res[from],704);
				array_push($res[to],70412);
				array_push($res[from],705);
				array_push($res[to],70512);
			}		
			if ($mres == ANYRES) {
				array_push($res[from],701);
				array_push($res[to],70112);
				array_push($res[from],702);
				array_push($res[to],70212);
				array_push($res[from],703);
				array_push($res[to],70312);
				array_push($res[from],704);
				array_push($res[to],70412);
				array_push($res[from],705);
				array_push($res[to],70512);
				array_push($res[from],201);
				array_push($res[to],20111);
				array_push($res[from],210);
				array_push($res[to],21011);
				array_push($res[from],211);
				array_push($res[to],21111);
				array_push($res[from],221);
				array_push($res[to],22111);
				array_push($res[from],501);
				array_push($res[to],50111);
				array_push($res[from],511);
				array_push($res[to],51111);					
			}	
			if ($mres == LIMITED) {
				array_push($res[from],701);
				array_push($res[to],70121);
				array_push($res[from],701);
				array_push($res[to],70122);

				array_push($res[from],702);
				array_push($res[to],70221);
				array_push($res[from],702);
				array_push($res[to],70222);

				array_push($res[from],703);
				array_push($res[to],70321);
				array_push($res[from],703);
				array_push($res[to],70322);

				array_push($res[from],704);
				array_push($res[to],70421);
				array_push($res[from],704);
				array_push($res[to],70422);

				array_push($res[from],705);
				array_push($res[to],70521);
				array_push($res[from],705);
				array_push($res[to],70522);
			}
			return $res;
	}




	function getpossibles($id) {
	
		$poss = array();
	
		if (($id%100) == 01) {
			$poss[ANYFOOD] = 2;
			$poss[HABITAT] = 1;
			$poss[ENERGY]  = 1;
			$poss[ANYRES]  = 1;
		}
		if (($id%100) == 02) {
			$poss[ANYFOOD] = 2;
			$poss[HABITAT] = 1;
			$poss[ENERGY]  = 1;
			$poss[ANYRES]  = 1;
		}
		if (($id%100) == 03) {
			$poss[LANDFOOD] = 2;
			$poss[HABITAT] = 1;
			$poss[ENERGY]  = 1;
			$poss[ANYRES]  = 1;
		}		
		if (($id%100) == 05) {
			$poss[WATERFOOD] = 1;
			$poss[HABITAT] = 1;
			$poss[ENERGY]  = 2;
			$poss[ANYRES]  = 1;
		}	
		if (($id%100) == 11) {
			$poss[HABITAT] = 2;
			$poss[ENERGY]  = 2;
			$poss[ANYRES]  = 2;
		}		
		if (($id%100) == 13) {
			$poss[HABITAT] = 1;
			$poss[ENERGY]  = 2;
			$poss[ANYRES]  = 1;
		}		
		if (($id%100) == 15) {
			$poss[ENERGY]  = 1;
			$poss[ANYFOOD]  = 1;
			$poss[ANYRES]  = 2;
		}				
		if (($id%100) == 31) {
			$poss[HABITAT]  = 2;
			$poss[ORE]  = 3;
		}			

		return $poss;
	}





	if ($data[sizew] != 10) {
		$bonusfields = $bonusfields - 1;
		if ($bonusfields > 0) $limited = rand(1,6);	
	} else {
		if ($bonusfields > 0) $limited = rand(1,4);
	}
	
	if ($limited == 1) $limited = 1;
	else $limited = 0;
	
	$nolimited = $bonusfields - $limited;
	
	
	$bphases = 0;
	
	// Bonus Phases
	

	unset($taken);
	
	if ($limited > 0)
	{
		$bphase[$bphases][mode] = "nocluster";
		$bphase[$bphases][description] = "Bonusfeld Dili oder Trita";
		$bphase[$bphases][num] = $limited;
		$bphase[$bphases][from] = array();
		$bphase[$bphases][to]   = array();
		
		$br = gettransformations(LIMITED);
		$bphase[$bphases][from] = $br[from];
		$bphase[$bphases][to] = $br[to];	
		
		$bphase[$bphases][adjacent] = 0;
		$bphase[$bphases][noadjacent] = 0;
		$bphase[$bphases][noadjacentlimit] = 0;	
		$bphase[$bphases][fragmentation] = 100;	
		$bphases++;
	}
	
	
	
	for($i = 0; $i < $nolimited;$i++) {
		$bphase[$bphases][mode] = "nocluster";
		$bphase[$bphases][description] = "Bonusfeld";
		$bphase[$bphases][num] = 1;
		$bphase[$bphases][from] = array();
		$bphase[$bphases][to]   = array();
		$arr = array();
		
		$poss = getpossibles($id);		
		
		
		foreach($poss as $key => $val) {
			$arr = madd($arr,$key,$val - $taken[$key]);
		}
		
		$mres = array_shift($arr);
		$taken[$mres]++;
		
	
		$br = gettransformations($mres);
		$bphase[$bphases][from] = $br[from];
		$bphase[$bphases][to] = $br[to];	
	
	
		$bphase[$bphases][adjacent] = 0;
		$bphase[$bphases][noadjacent] = 0;
		$bphase[$bphases][noadjacentlimit] = 0;	
		$bphase[$bphases][fragmentation] = 100;	
		$bphases++;
	}

	
	
?>
