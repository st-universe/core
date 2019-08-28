<?php
include_once(__DIR__.'/../../inc/config.inc.php');

if (!request::indInt('buildingid')) {
	exit;
}
$building = new Building(request::indInt('buildingid'));
if (request::postArray('fields')) {
	$reg = array();
	FieldBuilding::truncate('WHERE buildings_id='.$building->getId());
	foreach (request::postArray('fields') as $key => $type) {
		if (!$type || isset($reg[$type])) {
			continue;
		}
		$obj = new FieldBuildingData;
		$obj->setBuildingId($building->getId());
		$obj->setType(intval($type));
		$obj->save();
		$reg[$type] = 1;
	}
}

echo '<form method="post" action="buildingfieldassigner.php">
	<input type="hidden" name="buildingid" value="'.$building->getId().'" />
	Einstellungen für '.$building->getName().'<br />';
foreach ($building->getFieldList() as $key => $obj) {
	echo '<input type="text" size="6" name="fields[]" value="'.$obj->getType().'" />';
}
$i = 0;
while($i<15) {
	echo '<input type="text" size="6" name="fields[]" />';
	$i++;
}
echo '<input type="submit" value="OK" /></form><br /><br />
	<a href="building.php">Zurück</a>';
?>
