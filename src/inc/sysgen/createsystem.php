<?php
require_once('inc/config.inc.php');
require_once('control/db.class.php');
require_once('class/basetable.class.php');

	function draw($arr) {
		$p = rand(1,100);
		$c = 0;
		for ($i = 0; $i < 20; $i++) {
			$c += $arr[$i];
			if ($p <= $c) return $i;
		}
		return 0;
	}
	
	function pdraw($arr) {
		$p = rand(1,100);
		$c = 0;
		for ($i = 100; $i < 200; $i++) {
			$c += $arr[$i];
			if ($p <= $c) return $i;
		}
		return 0;
	}
	
	function weightcompare($a, $b)
	{
		if ($a[weight] < $b[weight]) return +1;
		if ($a[weight] > $b[weight]) return -1;
		return (rand(1,3)-2);
	}	

	function weighteddraw($a,$fragmentation=0)
	{
		for ($i = 0; $i < count($a);$i++)
		{
			$a[$i][weight] = rand(1,ceil($a[$i][baseweight] + $fragmentation));
		}
		usort($a,'weightcompare');
		return $a[0];
	}

	function getweightinglist($colfields,$mode,$from,$to,$adjacent,$noadjacent,$noadjacentlimit=0)
	{
		$w = count($colfields);
		$h = count($colfields[1]);
		$c = 0;
		for ($i = 1; $i <= $h; $i++)
		{
			for ($j = 1; $j <= $w; $j++)
			{
				$skip = 1;
				for ($k = 0; $k < count($from); $k++)
				{
					if ($colfields[$j][$i] == $from[$k]) $skip = 0;
				}
				if ($skip == 1) continue;
	
				$bw = 1;
			
				if ($mode != "nocluster" && $mode != "forced adjacency") 
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
		$colfields[affx] = 0;
		$colfields[affy] = 0;
		for ($i = 0; $i < $phase[$p][num]; $i++)
		{
			$arr = getweightinglist($colfields,$phase[$p][mode],$phase[$p][from],$phase[$p][to],$phase[$p][adjacent],$phase[$p][noadjacent],$phase[$p][noadjacentlimit]);
			if (count($arr) == 0) { 
				break;
			}
			
			$field = weighteddraw($arr,$phase[$p][fragmentation]);
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
			$colfields[affx] = $field[x];
			$colfields[affy] = $field[y];
		}
		return $colfields;
	}
	
	function polarToXY($angle,$distance)
	{
		$angle = $angle * pi() / 180;
		$res[x] = floor(cos($angle) * $distance);
		$res[y] = floor(sin($angle) * $distance);
		return $res;
	}
	
	function collides($star1, $star2)
	{
		$ul1x = $star1[x] - floor($star1[width]/2);
		$ul1y = $star1[y] - floor($star1[width]/2);

		$ul2x = $star2[x] - floor($star2[width]/2);
		$ul2y = $star2[y] - floor($star2[width]/2);

		for ($i = ($ul1x-$star1[iborder]); $i <= ($ul1x + $star1[width] + $star1[iborder]-1); $i++)
		{
			for ($j = ($ul1y-$star1[iborder]); $j <= ($ul1y + $star1[width] + $star1[iborder]-1); $j++)
			{
				$fields[$i][$j] = 1;
			}		
		}

		for ($i = $ul1x; $i <= ($ul1x + $star1[width]-1); $i++)
		{
			for ($j = $ul1y; $j <= ($ul1y + $star1[width]-1); $j++)
			{
				$fields[$i][$j] = 2;
			}		
		}

		for ($i = ($ul2x-$star2[iborder]); $i <= ($ul2x + $star2[width] + $star2[iborder]-1); $i++)
		{
			for ($j = ($ul2y-$star2[iborder]); $j <= ($ul2y + $star2[width] + $star2[iborder]-1); $j++)
			{
				if ($fields[$i][$j] == 2) return true;
				
			}		
		}

		for ($i = $ul2x; $i <= ($ul2x + $star2[width]-1); $i++)
		{
			for ($j = $ul2y; $j <= ($ul2y + $star2[width]-1); $j++)
			{
				if ($fields[$i][$j] == 2 || $fields[$i][$j] == 1) return true;
			}		
		}

		
		return false;
	}
	
	function makeCoreZone($star,$stars)
	{
		$star[0][x] = 0;
		$star[0][y] = 0;	
		$a = rand(1,360);
		
		if ($stars == 2)
		{
			$distance = 0;
			$c = polarToXY($a,$distance);
			$star[1][x] = $c[x];
			$star[1][y] = $c[y];
			while (collides($star[0],$star[1]))
			{
				$distance++;
				$c = polarToXY($a,$distance);
				$star[1][x] = $c[x];
				$star[1][y] = $c[y];
			}
		}
		elseif ($stars == 3)
		{
			$distance = 0;
			$c = polarToXY($a,$distance);
			$star[1][x] = $c[x];
			$star[1][y] = $c[y];
			while (collides($star[0],$star[1]))
			{
				$distance++;
				$c = polarToXY($a,$distance);
				$star[1][x] = $c[x];
				$star[1][y] = $c[y];
			}
			$a += 60;
			$distance = 0;
			$c = polarToXY($a,$distance);
			$star[2][x] = $c[x];
			$star[2][y] = $c[y];
			while (collides($star[1],$star[2]) || collides($star[0],$star[2]))
			{
				$distance++;
				$c = polarToXY($a,$distance);
				$star[2][x] = $c[x];
				$star[2][y] = $c[y];
			}			
		}
		elseif ($stars == 4)
		{
			$distance = 0;
			$c = polarToXY($a,$distance);
			$star[1][x] = $c[x];
			$star[1][y] = $c[y];
			while (collides($star[0],$star[1]))
			{
				$distance++;
				$c = polarToXY($a,$distance);
				$star[1][x] = $c[x];
				$star[1][y] = $c[y];
			}
			$a += 90;
			$distance = 0;
			$c = polarToXY($a,$distance);
			$star[2][x] = $c[x];
			$star[2][y] = $c[y];
			while (collides($star[1],$star[2]) || collides($star[0],$star[2]))
			{
				$distance++;
				$c = polarToXY($a,$distance);
				$star[2][x] = $c[x];
				$star[2][y] = $c[y];
			}			
			$a -= 45;
			$distance = 0;
			$c = polarToXY($a,$distance);
			$star[3][x] = $c[x];
			$star[3][y] = $c[y];
			while (collides($star[0],$star[3]) || collides($star[1],$star[3]) || collides($star[2],$star[3]))
			{
				$distance++;
				$c = polarToXY($a,$distance);
				$star[3][x] = $c[x];
				$star[3][y] = $c[y];
			}			
			
		}
	
	
		return centralize($star,$stars);
	}
	
	function randround($x)
	{
		$r = rand(1,2);
		if ($r == 1) return ceil($x);
		else return floor($x);
	}
	
	function centralize($star,$stars)
	{
		$minx =  1000;
		$miny =  1000;
		$maxx = -1000;
		$maxy = -1000;
		
		for ($i = 0; $i < $stars; $i++) 
		{
			$ulx = $star[$i][x] - floor($star[$i][width]/2);
			$uly = $star[$i][y] - floor($star[$i][width]/2);

			$infx = $ulx - $star[$i][oborder];
			$infy = $uly - $star[$i][oborder];
		
			$supx = $ulx + $star[$i][width] + $star[$i][oborder] - 1;
			$supy = $uly + $star[$i][width] + $star[$i][oborder] - 1;
		
			if ($infx < $minx) {
				$borderxstar = $i;
				$borderx = $ulx - $star[$i][oborder];
			}
			if ($infy < $miny) {
				$borderystar = $i;
				$bordery = $uly - $star[$i][oborder];
			}
		
			$minx = min($minx,$infx);
			$miny = min($miny,$infy);
			
			$maxx = max($maxx,$supx);
			$maxy = max($maxy,$supy);
		}
	
		$width  = $maxx - $minx + 1;
		$height = $maxy - $miny + 1;
	
		$heightadd = 0;
		if (($width % 2) == 0) 
		{
			if (($height % 2) == 1) 
			{
				$height++;
				$heightadd = rand(0,1);
			}
			$even = 1;
			$odd = 0;
		} else {
			if (($height % 2) == 0) 
			{
				$height++;
				$heightadd = rand(0,1);
			}
			$even = 0;
			$odd = 1;		
		}
	
	
		$total = max($width,$height);
	
		if ($total > $width) $conx = ($total-$width)/2;
		if ($total > $height) $cony = ($total-$height)/2;
	
		$newx =  1;
		$newy =  1;
		
		
		$addx = $newx + $conx - $borderx;
		$addy = $newy + $cony - $bordery + $heightadd;
		
		
		for ($i = 0; $i < $stars; $i++) 
		{
			$ulx = $star[$i][x] - floor($star[$i][width]/2);
			$uly = $star[$i][y] - floor($star[$i][width]/2);

			$star[$i][x] = $ulx + $addx;
			$star[$i][y] = $uly + $addy;
		}
		
		$res[star] = $star;
		$res[even] = $even;
		$res[odd] = $odd;
		$res[width] = $total;
		return $res;
	}
	
	function distance($x,$y,$m,$n)
	{
		return sqrt(($x-$m)*($x-$m) + ($y-$n)*($y-$n));
	}
	
	
	function generate($id)
	{
		include("incSystems/".$id.".php");

		$r = makeCoreZone($star,$stars);
		
				
		$syswidth = $r[odd] + 2*$data[radius];
		if ($syswidth < $r[width]) $syswidth = $r[width];
		
		$borderadd = ($syswidth-$r[width])/2;
		
		for ($i = 1; $i <= $syswidth; $i++)
		{
			for ($j = 1; $j <= $syswidth; $j++)
			{
				$fields[$i][$j] = 1;
			}
		}
					
		for ($i = 1; $i <= $r[width]; $i++)
		{
			for ($j = 1; $j <= $r[width]; $j++)
			{
				$fields[$i+$borderadd][$j+$borderadd] = 0;
			}
		}
		
		$starrad = (1.4 * $r[width]/2);
		$m = ($syswidth+1)/2;
		for ($i = 1; $i <= $syswidth; $i++)
		{
			for ($j = 1; $j <= $syswidth; $j++)
			{
				if (($fields[$i][$j] == 1) && (distance($i,$j,$m,$m) < $starrad)) {
					$fields[$i][$j] = 0;
				}
				if ($i <= 1 || $i >= $syswidth || $j <= 1 || $j >= $syswidth) $fields[$i][$j] = 0;
			}
		}
		
		for ($i = 0; $i < $stars; $i++)
		{
			for ($k = 1; $k <= $r[star][$i][width]; $k++)
			{
				for ($l = 1; $l <= $r[star][$i][width]; $l++)
				{
					$xfadd = "".$k;
					$yfadd = "".$l;
					if ($k < 10) $xfadd = "0".$xfadd;
					if ($l < 10) $yfadd = "0".$yfadd;
					
					$fields[$k+$r[star][$i][x]-1+$borderadd][$l+$r[star][$i][y]-1+$borderadd] = $star[$i][type].$xfadd.$yfadd;
				}
			}
		}
				
		$min = 1000;
		$max = -1000;
		
		for ($i = 1; $i <= $syswidth; $i++)
		{
			for ($j = 1; $j <= $syswidth; $j++)
			{
				if ($fields[$i][$j] == 1)
				{
					$d = round(distance($i,$j,$m,$m));
					$min = min($d,$min);
					$max = max($d,$max);
					$fields[$i][$j] = $d;
				}
			}
		}				
		
		for ($i = 1; $i <= $syswidth; $i++)
		{
			for ($j = 1; $j <= $syswidth; $j++)
			{
				if (($fields[$i][$j] >= 0) && ($fields[$i][$j] <= 30))
				{
					$d = round(distance($i,$j,$m,$m));
					$z = 1;
					if (($d - $min) > $zone[1]/100*($max-$min)) $z++;
					if (($d - $min) > ($zone[1]+$zone[2])/100*($max-$min)) $z++;
					if (($d - $min) > ($zone[1]+$zone[2]+$zone[3])/100*($max-$min)) $z++;
					$zones[$i][$j] = $z;
					if ($fields[$i][$j] > $max-1) $fields[$i][$j] = 0;
				}
			}
		}	
		$min++;
		$max--;
	
		$beltmax = $fields[1][round($syswidth/2)];
		$beltmin = $min;
		
		$m = ceil($syswidth/2);
		
		for ($b = 1; $b <= $belts; $b++)
		{
			include("incBelts/".$belt[$b].".php");
			
			$foo = 0;
			for ($i = 3; $i <= $m; $i++)
			{
				if (($fields[$i][$m] > 0) && ($fields[$i][$m] < 40)) 
				{
					$bar[$foo] = $fields[$i][$m];
					$foo++;
				}
			}
			$beltinner = $bar[rand(0,$foo-1)];
			
			if ($isthick == 1)
			{
			
				$bphase[1][from] = array("0" => $beltinner);
				$bphase[1][to] = array("0" => (600+2*$b));
				$bphase[1][num] = 200;

				$bphase[2][from] = array("0" => ($beltinner+1));
				$bphase[2][to] = array("0" => (601+2*$b));
				$bphase[2][num] = 200;
				
				for ($p = 1; $p <= $bphases; $p++)
				{
					$fields = dophase($p,$bphase,$fields);
				}
				
				$thincount = 0;
				$thickcount = 0;
				for ($i = 1; $i <= $syswidth; $i++)
				{
					for ($j = 1; $j <= $syswidth; $j++)
					{
						if ($fields[$i][$j] == 600+2*$b) $thickcount++;
						if ($fields[$i][$j] == 601+2*$b) $thincount++;
					}
				}	

				$thickcount = ceil($thickcount * ($density/100)); 
				$thincount = ceil($thincount * ($density/(100*$degradation))); 
				
				$aphase[0][mode] = "nocluster";
				$aphase[0][description] = "Asteroid";
				$aphase[0][num] = $thickcount;
				if ($isthick == 1)
				{
					$aphase[0][from] = array("0" => ($invert+600+2*$b),"1" => ($thin));
					$aphase[0][to]   = array("0" => $thin, "1" => $thick);
				} else {
					$aphase[0][from] = array("0" => ($invert+600+2*$b));
					$aphase[0][to]   = array("0" => $thin);			
				}
				$aphase[0][adjacent] = 0;
				$aphase[0][noadjacent] = 0;
				$aphase[0][noadjacentlimit] = 0;	
				$aphase[0][fragmentation] = $fragmentation;				

				$fields = dophase(0,$aphase,$fields);
				
				$aphase[0][mode] = "normal";
				$aphase[0][description] = "Asteroid";
				$aphase[0][num] = $thincount;
				$aphase[0][from] = array("0" => (601-$invert+2*$b));
				$aphase[0][to]   = array("0" => $thin);
				$aphase[0][adjacent] = 0;
				$aphase[0][noadjacent] = 0;
				$aphase[0][noadjacentlimit] = 0;	
				$aphase[0][fragmentation] = $fragmentation;				

				$fields = dophase(0,$aphase,$fields);
			
			} else {
			
				$bphase[1][from] = array("0" => $beltinner);
				$bphase[1][to] = array("0" => (600+2*$b));
				$bphase[1][num] = 200;

				$bphase[2][from] = array("0" => ($beltinner+1));
				$bphase[2][to] = array("0" => (601+2*$b));
				$bphase[2][num] = 200;
				
				for ($p = 1; $p <= $bphases; $p++)
				{
					$fields = dophase($p,$bphase,$fields);
				}
				
				$thincount = 0;
				$thickcount = 0;
				for ($i = 1; $i <= $syswidth; $i++)
				{
					for ($j = 1; $j <= $syswidth; $j++)
					{
						if ($fields[$i][$j] == 600+2*$b) $thickcount++;
						if ($fields[$i][$j] == 601+2*$b) $thincount++;
					}
				}	

				$thickcount = ceil($thickcount * ($density/100)); 
				$thincount = ceil($thincount * ($density/100)); 
				
				$aphase[0][mode] = "normal";
				$aphase[0][description] = "Asteroid";
				$aphase[0][num] = $thincount+$thickcount;
				$aphase[0][from] = array("0" => (601+2*$b),"1" => (600+2*$b));
				$aphase[0][to]   = array("0" => $thin,"1" => $thin);
				$aphase[0][adjacent] = 0;
				$aphase[0][noadjacent] = 0;
				$aphase[0][noadjacentlimit] = 0;	
				$aphase[0][fragmentation] = $fragmentation;				

				$fields = dophase(0,$aphase,$fields);
			
			
			}
		}

		for ($i = 1; $i <= $syswidth; $i++)
		{
			for ($j = 1; $j <= $syswidth; $j++)
			{
				if (($fields[$i][$j] >= 600) && ($fields[$i][$j] <= 699)) $fields[$i][$j] = 50;
				if (($fields[$i][$j] >= 700) && ($fields[$i][$j] <= 799))
				{
					if (($fields[$i+1][$j+1] >= 1) && ($fields[$i+1][$j+1] <= 30)) $fields[$i+1][$j+1] = 50;
					if (($fields[$i+1][$j] >= 1) && ($fields[$i+1][$j] <= 30)) $fields[$i+1][$j] = 50;
					if (($fields[$i-1][$j] >= 1) && ($fields[$i-1][$j] <= 30)) $fields[$i-1][$j] = 50;
					if (($fields[$i][$j+1] >= 1) && ($fields[$i][$j+1] <= 30)) $fields[$i][$j+1] = 50;
					if (($fields[$i][$j-1] >= 1) && ($fields[$i][$j-1] <= 30)) $fields[$i][$j-1] = 50;
					if (($fields[$i+1][$j-1] >= 1) && ($fields[$i+1][$j-1] <= 30)) $fields[$i+1][$j-1] = 50;
					if (($fields[$i-1][$j-1] >= 1) && ($fields[$i-1][$j-1] <= 30)) $fields[$i-1][$j-1] = 50;
					if (($fields[$i-1][$j+1] >= 1) && ($fields[$i-1][$j+1] <= 30)) $fields[$i-1][$j+1] = 50;
				}
			}
		}
		
		
		
		for ($i = 1; $i <= $syswidth; $i++)
		{
			for ($j = 1; $j <= $syswidth; $j++)
			{
				if (($fields[$i][$j] != 0) && ($fields[$i][$j] <= 30))
				{
					include("zonePlanets.php");
					$fields[$i][$j] = $t;
				}
			}
		}						
		
		$planarr = array();
		$sysviewarr = array();
		
		for ($o = 1; $o <= $data[planets]; $o++)
		{
			include("singlePlanetPhase.php");
			include("singleMoonPhase.php");

			unset($mphase,$mphases,$pl);
			
			$fields = dophase(0,$spphase,$fields);
			
			$px = $fields[affx];
			$py = $fields[affy];

			$plantype = $fields[$px][$py];

			if (intval($px) == 0 || intval($py) == 0) break;
			
			include("incPlanets/".$plantype.".php");
			
			$mooncount = 0;
			
			for ($i = 1; $i <= $syswidth; $i++)
			{
				for ($j = 1; $j <= $syswidth; $j++)
				{
					if (($fields[$i][$j] >= 100) && ($fields[$i][$j] <= 200) && (floor(distance($i,$j,$px,$py)) <= $moonradius+2))
					{
						$fields[$i][$j] = 50;
					}
					if (($fields[$i][$j] == 50) && (ceil(distance($i,$j,$px,$py)) <= $moonradius))
					{
						$fields[$i][$j] = 60;
						$mooncount++;
					}
					
				}
			}

			include("incPlanets/".$plantype.".php");
			
			$pl[x] = $px;
			$pl[y] = $py;
			$pl[name] = "Planet";
			$pl[type] = $plantype;
			
			array_push($planarr,$pl);
			
			$pl[moons] = array();
								
			for ($i = 0; $i < $mphases; $i++)
			{
				$fields = dophase($i,$mphase,$fields);
			}

			for ($i = 0; $i < $moons; $i++)
			{
				unset($mo);
				$fields = dophase(0,$smphase,$fields);
				if (intval($fields[affx]) == 0 || intval($fields[affy]) == 0) break;
				
				$mo[x] = $fields[affx];
				$mo[y] = $fields[affy];
				$mo[name] = "Mond";
				$mo[type] = $fields[$fields[affx]][$fields[affy]];
				
				array_push($planarr,$mo);
				array_push($pl[moons],$mo);
			}
			
			array_push($sysviewarr,$pl);
			
			for ($i = 1; $i <= $syswidth; $i++)
			{
				for ($j = 1; $j <= $syswidth; $j++)
				{
					if ($fields[$i][$j] == 60) $fields[$i][$j] = 0;
					if (($fields[$i][$j] >= 400) && ($fields[$i][$j] <= 499)) $fields[$i][$j] = 0;
					
				}
			}
		}
		
				
		for ($i = 1; $i <= $syswidth; $i++)
		{
			for ($j = 1; $j <= $syswidth; $j++)
			{
				if ($fields[$i][$j] == 0) $fields[$i][$j] = 1;
				if ($fields[$i][$j] == 50) $fields[$i][$j] = 1;
				if (($fields[$i][$j] >= 100) && ($fields[$i][$j] <= 199)) $fields[$i][$j] = 1;
			}
		}
				
				
		$fields[width] = $syswidth;
		
		
		// HALLO WOLV, HIER DER INTERESSANTE TEIL
		// Einfügen in die DB
		
		require_once("class/starsystem.class.php");
		require_once("class/colony.class.php");
		require_once("class/systemmap.class.php");

		$datasystem = new StarSystemData;
		
		$datasystem->setType($id);
		$datasystem->setCX(0);
		$datasystem->setCY(0);
		$datasystem->setMaxX($syswidth);
		$datasystem->setMaxY($syswidth);		
		$datasystem->setBonusFields(3);
		$datasystem->save();


		foreach($planarr as $val)
		{
			$datacol = new ColonyData;

			$datacol->setSystemsId($datasystem->getId());
			$datacol->setSX($val[x]);
			$datacol->setSY($val[y]);
			$datacol->setPlanetName($val[name]);
			$datacol->setColonyClass($val[type]);
			$datacol->save();

			unset($datacol);
		}
		

		for ($iy = 1; $iy <= $syswidth; $iy++)
		{
			for ($ix = 1; $ix <= $syswidth; $ix++)
			{
				$datafield = new SystemMapData;
				$datafield->setSX($ix);
				$datafield->setSY($iy);
				$datafield->setSystemId($datasystem->getId());
				$datafield->setFieldId($fields[$ix][$iy]);
				$datafield->save();
				
				unset($datafield);
			}
		}
		
		
		return $datasystem;
	}
	
	function showsys($fields)
	{
		for ($i = 1; $i <= $fields[width]; $i++)
		{
			echo "<br>";
			for ($j = 1; $j <= $fields[width]; $j++)
			{
				
				$color = "FFFFFF";
				if ($fields[$j][$i] == 0) $color = "FF0000";
				if ($fields[$j][$i] == 1) $color = "333333";
				if ($fields[$j][$i] == 50) $color = "449944";
				if ($fields[$j][$i] == 60) $color = "994444";
				if (($fields[$j][$i] >= 100) && ($fields[$j][$i] <= 199)) $color = "444499";
				if (($fields[$j][$i] >= 200) && ($fields[$j][$i] <= 299)) $color = "9999FF";
				if (($fields[$j][$i] >= 300) && ($fields[$j][$i] <= 399)) $color = "666699";
				
				if (($fields[$j][$i] >= 700) && ($fields[$j][$i] <= 799)) $color = "AAAAAA";
				if ($fields[$j][$i] > 999) $color = "FFFF44";
				
				if ($fields[$j][$i] == 2) $color = "44FF44";
				if ($fields[$j][$i] == 3) $color = "4444FF";
				if ($fields[$j][$i] == 4) $color = "FF44FF";
				
				echo "<font color=".$color.">";

				if ($fields[$j][$i] < 10) echo "0";
				if ($fields[$j][$i] < 100) echo "0";
				if ($fields[$j][$i] < 1000) echo "0";
				if ($fields[$j][$i] >= 100000) {
					if ($fields[$j][$i]%10000 < 1000) echo "0";
					echo ($fields[$j][$i]%10000)." ";
				}
				else echo $fields[$j][$i]." ";
				echo "</font>";
			}
		}
	}
	
	function showpics($fields)
	{
		for ($i = 1; $i <= $fields[width]; $i++)
		{
			echo "<br>";
			for ($j = 1; $j <= $fields[width]; $j++)
			{
				echo "<img src='pics/".$fields[$j][$i].".gif' border=0>";
			}
		}
	}
	

	include("systemList.php");
	
?>
