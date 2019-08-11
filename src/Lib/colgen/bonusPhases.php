<?php
	
	if ($data[sizew] != 10) {
		$bonusfields = $bonusfields - 1;
	}
	
	$bftaken = 0;
	$phasesSuper 	= 0;
	$phasesOre 		= 0;
	$phasesDeut 	= 0;
	$phasesResource	= 0;
	$phasesOther	= 0;
	
	if (($bftaken < $bonusfields) && (rand(1,100) <= 15)) {
		$phasesSuper 	+= 1;
		$bftaken		+= 1;
	}
	if (($bftaken < $bonusfields) && (rand(1,100) <= 80)) {
		$phasesResource	+= 1;
		$bftaken		+= 1;
	}
	if (($phasesSuper == 0) && ($data[sizew] > 7)) {
		if (($bftaken < $bonusfields) && (rand(1,100) <= 10)) {
			$phasesResource	+= 1;
			$bftaken		+= 1;
		}
	}
	
	if ($bftaken < $bonusfields) {
		$restcount = $bonusfields - $bftaken;
		
		$phasesOther	+= $restcount;
		$bftaken		+= $restcount;
	}
	
	// echo "<br>".$phasesSuper." ".$phasesOre." ".$phasesDeut." ".$phasesResource." ".$phasesOther;
	// echo "<br>".$phasesSuper." ".$phasesOre." ".$phasesDeut." ".$phasesResource." ".$phasesOther;
	
	$bphases = 0;
	
	// Bonus Phases

	unset($taken);
	
	for($i = 0; $i < $phasesSuper;$i++)
	{
		$bphase[$bphases] = $this->createBonusPhase(BONUS_SUPER);
		$bphases++;
	}
	
	for($i = 0; $i < $phasesResource;$i++)
	{
		$bphase[$bphases] = $this->createBonusPhase(BONUS_ARES);
		$bphases++;
	}

	for($i = 0; $i < $phasesDeut;$i++)
	{
		$bphase[$bphases] = $this->createBonusPhase(BONUS_DEUT);
		$bphases++;
	}

	for($i = 0; $i < $phasesOre;$i++)
	{
		$bphase[$bphases] = $this->createBonusPhase(BONUS_ORE);
		$bphases++;
	}
	
	
	for($i = 0; $i < $phasesOther;$i++) 
	{
		if (count($bonusdata) == 0) break;
	
		shuffle($bonusdata);
		$next = array_shift($bonusdata);
	
		$bphase[$bphases] = $this->createBonusPhase($next);
		$bphases++;
	}

	
	
?>
