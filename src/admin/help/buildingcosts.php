<?php

include_once(__DIR__.'/../../inc/config.inc.php');

if (!request::indInt('buildingid')) {
	exit;
}
$building = new Building(request::indInt('buildingid'));
if (request::postArray('costs')) {
	$reg = array();
	$goods = request::postArray('goodid');
	BuildingCost::truncate('WHERE buildings_id='.$building->getId());
	foreach (request::postArray('costs') as $key => $count) {
		if (!$count || !isset($goods[$key]) || isset($reg[$goods[$key]])) {
			continue;
		}
		$obj = new BuildingCostData;
		$obj->setBuildingId($building->getId());
		$obj->setGoodId($goods[$key]);
		$obj->setCount(intval($count));
		$obj->save();
		$reg[$goods[$key]] = 1;
	}
	$building->setEpsCost(request::postString('eps'));
	$building->save();
}

echo '<form method="post" action="buildingcosts.php">
	<input type="hidden" name="buildingid" value="'.$building->getId().'" />
	Baukosten für '.$building->getName().'<br /><b>Goodid</b> <b>Count</b><br />
	<img src="../../assets/buttons/e_trans1.gif" /> <input type="text" value="'.$building->getEpsCost().'" size="4" name="eps" /><br />';
foreach ($building->getCosts() as $key => $obj) {
	echo '<img src="../../assets/goods/'.$obj->getGoodId().'.gif" /> <input type="text" size="4" name="goodid[]" value="'.$obj->getGoodId().'" /> <input type="text" size="4" name="costs[]" value="'.$obj->getCount().'" /><br />';
}
$i = 0;
while($i<15) {
	echo '<input type="text" size="4" name="goodid[]" <input type="text" size="4" name="costs[]" /><br />';
	$i++;
}
echo '<input type="submit" value="OK" /></form><br /><br />
	<a href="building.php">Zurück</a>';

?>
