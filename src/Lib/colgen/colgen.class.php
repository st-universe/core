<?php

define("BONUS_AFOOD",1);
define("BONUS_LFOOD",2);
define("BONUS_WFOOD",3);
	
define("BONUS_HABI",10);

define("BONUS_ARES",20);
define("BONUS_ORE",22);
define("BONUS_DEUT",21);

	
define("BONUS_AENER",30);
define("BONUS_SENER",31);
define("BONUS_WENER",32);
	
define("BONUS_SUPER",99);
	
class ColonyGenerator{

	private $datapath = "coldata/";

	function __construct() {
		$this->datapath = "coldata/";
	}
	
	function weighteddraw($a,$fragmentation=0)
	{
		for ($i = 0; $i < count($a);$i++)
		{
			$a[$i][weight] = rand(1,ceil($a[$i][baseweight] + $fragmentation));
		}
		usort($a,	function ($a, $b)
					{
						if ($a[weight] < $b[weight]) return +1;
						if ($a[weight] > $b[weight]) return -1;
						return (rand(1,3)-2);
					});
					
		return $a[0];
	}
	
	function madd($arr,$ele,$cnt) {
		for ($i = 0; $i < $cnt; $i++) {
			array_push($arr,$ele);
		}
		shuffle($arr);
		return $arr;
	}
	

	function shadd($arr,$fld,$bonus) {

		array_push($arr[from],	$fld);
		array_push($arr[to],	$fld.$bonus);

		return $arr;
	}
	
	function getBonusFieldTransformations($btype) {
		$res = array();
		$res[from] = array();
		$res[to] = array();
	
		if (($btype == BONUS_LFOOD) || ($btype == BONUS_AFOOD)) {
			$res = $this->shadd($res,101,"01");
			$res = $this->shadd($res,111,"01");
			$res = $this->shadd($res,112,"01");
			$res = $this->shadd($res,121,"01");
			$res = $this->shadd($res,601,"01");
			$res = $this->shadd($res,611,"01");
			$res = $this->shadd($res,602,"01");
		}
		if (($btype == BONUS_WFOOD) || ($btype == BONUS_AFOOD)) {
			$res = $this->shadd($res,201,"02");
			$res = $this->shadd($res,211,"02");
			$res = $this->shadd($res,221,"02");
		}	
		if ($btype == BONUS_HABI)  {
			$res = $this->shadd($res,101,"03");
			$res = $this->shadd($res,111,"03");
			$res = $this->shadd($res,112,"03");
			$res = $this->shadd($res,601,"03");
			$res = $this->shadd($res,601,"04");
			$res = $this->shadd($res,602,"03");
			$res = $this->shadd($res,611,"03");
			$res = $this->shadd($res,611,"04");
			$res = $this->shadd($res,713,"04");
			$res = $this->shadd($res,715,"04");
			$res = $this->shadd($res,725,"04");
		}	
	
		// solar
		if (($btype == BONUS_SENER) || ($btype == BONUS_AENER)) {
			$res = $this->shadd($res,401,"31");
			$res = $this->shadd($res,402,"31");
			$res = $this->shadd($res,403,"31");
			$res = $this->shadd($res,404,"31");
			$res = $this->shadd($res,713,"31");
		}
		
		// strömung
		if (($btype == BONUS_WENER) || ($btype == BONUS_AENER)) {
			$res = $this->shadd($res,201,"32");
			$res = $this->shadd($res,221,"32");
		}

		if (($btype == BONUS_ORE) || ($btype == BONUS_ARES)) {
			$res = $this->shadd($res,701,"12");
			$res = $this->shadd($res,702,"12");
			$res = $this->shadd($res,703,"12");
			$res = $this->shadd($res,704,"12");
			$res = $this->shadd($res,705,"12");
		}
		if (($btype == BONUS_DEUT) || ($btype == BONUS_ARES)) {
			$res = $this->shadd($res,201,"11");
			$res = $this->shadd($res,210,"11");
			$res = $this->shadd($res,211,"11");
			$res = $this->shadd($res,221,"11");
			$res = $this->shadd($res,501,"11");
			$res = $this->shadd($res,511,"11");
		}		

		if ($btype == BONUS_SUPER) {
		
			// dili
			$res = $this->shadd($res,701,"21");
			$res = $this->shadd($res,702,"21");
			$res = $this->shadd($res,703,"21");
			$res = $this->shadd($res,704,"21");
			$res = $this->shadd($res,705,"21");
			
			// trit
			$res = $this->shadd($res,701,"22");
			$res = $this->shadd($res,702,"22");
			$res = $this->shadd($res,703,"22");
			$res = $this->shadd($res,704,"22");
			$res = $this->shadd($res,705,"22");
		}
	
		return $res;
	}
	
	
	function createBonusPhase($btype) {
	
		$bphase = array();
	
		$bphase[mode] 			= "nocluster";
		$bphase[description] 	= "Bonusfeld";
		
		$br = $this->getBonusFieldTransformations($btype);

		$bphase[num]	= 1;
		$bphase[from] 	= $br[from];
		$bphase[to] 	= $br[to];
		
		$bphase[adjacent] = 0;
		$bphase[noadjacent] = 0;
		$bphase[noadjacentlimit] = 0;
		$bphase[fragmentation] = 100;
	
		return $bphase;
	}

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function getweightinglist($colfields,$mode,$from,$to,$adjacent,$noadjacent,$noadjacentlimit=0)
	{

		$w = $colfields[w]; //count($colfields);
		$h = count($colfields[0]);
		$c = 0;
		for ($i = 0; $i < $h; $i++)
		{
			for ($j = 0; $j < $w; $j++)
			{
				$skip = 1;
				for ($k = 0; $k < count($from); $k++)
				{
					if ($colfields[$j][$i] == $from[$k]) $skip = 0;
				}
				if ($skip == 1) continue;
	
				$bw = 1;
				if ((($mode == "polar") || ($mode == "strict polar")) && ($i == 0 || $i == $h-1)) $bw += 1;
				if (($mode == "polar seeding north") && ($i == 0)) $bw += 2;
				if (($mode == "polar seeding south") && ($i == $h-1)) $bw += 2;

				if (($mode == "equatorial") && (($i == 2 && $h == 5) || (($i == 2 || $i == 3) && $h == 6))) $bw += 1;
				
				if ($mode != "nocluster" && $mode != "forced adjacency" && $mode != "forced rim"  && $mode != "polar seeding north" && $mode != "polar seeding south")
				{
					for ($k = 0; $k < count($to); $k++)
					{
						if ($colfields[$j-1][$i] == $to[$k]) $bw += 1;
						if ($colfields[$j+1][$i] == $to[$k]) $bw += 1;
						if ($colfields[$j][$i-1] == $to[$k]) $bw += 1;
						if ($colfields[$j][$i+1] == $to[$k]) $bw += 1;
						if ($colfields[$j-1][$i-1] == $to[$k]) $bw += 0.5;
						if ($colfields[$j+1][$i+1] == $to[$k]) $bw += 0.5;
						if ($colfields[$j+1][$i-1] == $to[$k]) $bw += 0.5;
						if ($colfields[$j-1][$i+1] == $to[$k]) $bw += 0.5;
					}
				}

				if ((($mode == "polar seeding north") && ($i == 0)) || (($mode == "polar seeding south") && ($i == $h-1)))
				{
					for ($k = 0; $k < count($to); $k++)
					{
						if ($colfields[$j-1][$i] == $to[$k]) $bw += 2;
						if ($colfields[$j+1][$i] == $to[$k]) $bw += 2;
					}
				}
				
				if ($adjacent[0]) {
				for ($k = 0; $k < count($adjacent); $k++)
				{
					if ($colfields[$j-1][$i] == $adjacent[$k]) $bw += 1;
					if ($colfields[$j+1][$i] == $adjacent[$k]) $bw += 1;
					if ($colfields[$j][$i-1] == $adjacent[$k]) $bw += 1;
					if ($colfields[$j][$i+1] == $adjacent[$k]) $bw += 1;
					if ($colfields[$j-1][$i-1] == $adjacent[$k]) $bw += 0.5;
					if ($colfields[$j+1][$i+1] == $adjacent[$k]) $bw += 0.5;
					if ($colfields[$j+1][$i-1] == $adjacent[$k]) $bw += 0.5;
					if ($colfields[$j-1][$i+1] == $adjacent[$k]) $bw += 0.5;
				}}

				if ($noadjacent[0]) {
				for ($k = 0; $k < count($noadjacent); $k++)
				{
					$ad = 0;
					if ($colfields[$j-1][$i] == $noadjacent[$k]) $ad += 1;
					if ($colfields[$j+1][$i] == $noadjacent[$k]) $ad += 1;
					if ($colfields[$j][$i-1] == $noadjacent[$k]) $ad += 1;
					if ($colfields[$j][$i+1] == $noadjacent[$k]) $ad += 1;
					if ($colfields[$j-1][$i-1] == $noadjacent[$k]) $ad += 0.5;
					if ($colfields[$j+1][$i+1] == $noadjacent[$k]) $ad += 0.5;
					if ($colfields[$j+1][$i-1] == $noadjacent[$k]) $ad += 0.5;
					if ($colfields[$j-1][$i+1] == $noadjacent[$k]) $ad += 0.5;

					if ($ad > $noadjacentlimit) $bw = 0;
				}}

				if (($mode == "forced adjacency") && ($bw < 2)) $bw = 0;
				if (($mode == "forced rim") && ($bw < 1.5)) $bw = 0;
				
				if (($mode == "polar") && ($i > 1) && ($i < $h-2)) $bw = 0;
				if (($mode == "strict polar") && ($i > 0) && ($i < $h-1)) $bw = 0;
				if ($mode == "polar seeding north" && ($i > 1)) $bw = 0;
				if ($mode == "polar seeding south" && ($i < $h-2)) $bw = 0;
				if (($mode == "equatorial") && (($i < 2) || ($i > 3)) && ($h == 6)) $bw = 0;
				if (($mode == "equatorial") && (($i < 2) || ($i > 3)) && ($h == 5)) $bw = 0;
				
				if (($mode == "lower orbit") && ($i != 1)) $bw = 0;
				if (($mode == "upper orbit") && ($i != 0)) $bw = 0;

				if (($mode == "tidal seeding") && ($j != 0)) $bw = 0;
				
				if (($mode == "right") && ($colfields[$j-1][$i] != $adjacent[0])) $bw = 0;
				if (($mode == "below") && ($colfields[$j][$i-1] != $adjacent[0])) $bw = 0;
				if (($mode == "crater seeding") && (($j == $w-1) || ($i == $h-1))) {
					$bw = 0;
				}
					
				if ($bw > 0)
				{
					//echo "<br>".$bw." - ".$j."|".$i;
					$res[$c][x] = $j;
					$res[$c][y] = $i;
					$res[$c][baseweight] = $bw;
					$c++;
				}
			}
		}
		return $res;
	}

	function dophase($p,$phase,$colfields)
	{
		if ($phase[$p]['mode'] == "fullsurface") {
			$k = 0;
			for ($ih = 0; $ih < $colfields[h]; $ih++) {
				for ($iw = 0; $iw < $colfields[w]; $iw++) {

					$k++;
		
					$colfields[$iw][$ih] = $phase[$p][type] * 100 + $k;
				}
			}
		
		
		} else {
	
			for ($i = 0; $i < $phase[$p][num]; $i++)
			{
				$arr = $this->getweightinglist($colfields,$phase[$p][mode],$phase[$p][from],$phase[$p][to],$phase[$p][adjacent],$phase[$p][noadjacent],$phase[$p][noadjacentlimit]);
				if (count($arr) == 0) {
					break;
				}
				
				$field = $this->weighteddraw($arr,$phase[$p][fragmentation]);
				$ftype = $colfields[$field[x]][$field[y]];

				$t = 0;
				unset($ta);
				for ($c = 0; $c < count($phase[$p][from]); $c++)
				{

					if ($ftype == $phase[$p][from][$c]) {
						$ta[$t] = $phase[$p][to][$c];
						$t++;
					}
					
				}
				if ($t > 0) $colfields[$field[x]][$field[y]] = $ta[rand(0,$t-1)];
			}
			
		}
		return $colfields;
	}

	
	function combine($col,$orb,$gnd)
	{

		$q = 0;
		for ($i = 0; $i < $orb[h]; $i++)
		{
			for ($j = 0; $j < $orb[w]; $j++)
			{
				$res[$q] = $orb[$j][$i];
				$q++;
			}
		}
		
		for ($i = 0; $i < $col[h]; $i++)
		{
			for ($j = 0; $j < $col[w]; $j++)
			{
				$res[$q] = $col[$j][$i];
				$q++;
			}
		}
		
		for ($i = 0; $i < $gnd[h]; $i++)
		{
			for ($j = 0; $j < $gnd[w]; $j++)
			{
				$res[$q] = $gnd[$j][$i];
				$q++;
			}
		}

		//$res[length] = $q;
	
		return $res;
	
	}
	
	function generateColony($id,$bonusfields=2)
	{

		if (!file_exists(__DIR__.'/'.$this->datapath.$id.".php")) return false;

		$bonusdata = array();
		
		include($this->datapath.$id.".php");
		
		include("bonusPhases.php");
				
		$log = "";
				
		$h = $data[sizeh];
		$w = $data[sizew];

		for ($i = 0; $i < $h; $i++)
		{
			for ($j = 0; $j < $w; $j++)
			{
				$colfields[$j][$i] = $data[basefield];
			}
		}
		$colfields[h] = $h;
		$colfields[w] = $w;
		
		for ($i = 0; $i < 2; $i++)
		{
			for ($j = 0; $j < $w; $j++)
			{
				$orbfields[$j][$i] = $odata[basefield];
			}
		}
		$orbfields[h] = 2;
		$orbfields[w] = $w;
		
		$gndfields[h] = 0;
		if ($hasground)
		{
			for ($i = 0; $i < 2; $i++)
			{
				for ($j = 0; $j < $w; $j++)
				{
					$gndfields[$j][$i] = $udata[basefield];
				}
			}	
			$gndfields[h] = 2;
			$gndfields[w] = $w;
		}

		for ($i = 0; $i < $phases; $i++)
		{
			$log = $log ."<br>".$phase[$i][description];

			$colfields = $this->dophase($i,$phase,$colfields);
		}
		
		for ($i = 0; $i < $ophases; $i++)
		{
			$log = $log ."<br>".$ophase[$i][description];

			$orbfields = $this->dophase($i,$ophase,$orbfields);
		}
		
		for ($i = 0; $i < $uphases; $i++)
		{
			$log = $log ."<br>".$uphase[$i][description];

			$gndfields = $this->dophase($i,$uphase,$gndfields);
		}
		
		for ($i = 0; $i < $bphases; $i++)
		{
			$log = $log ."<br>".$bphase[$i][description];

			$colfields = $this->dophase($i,$bphase,$colfields);
		}
	
		return $this->combine($colfields,$orbfields,$gndfields);
	}

}


?>