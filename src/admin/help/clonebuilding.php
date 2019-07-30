<?php
include_once(__DIR__.'/../../inc/config.inc.php');

if (!request::indInt('buildingid')) {
	exit;
}
$building = new Building(request::indInt('buildingid'));
if (request::postInt('newid')) {
	Building::cloneBuilding($building->getId(),request::postInt('newid'));
}

echo '<form method="post" action="clonebuilding.php">
	<input type="hidden" name="buildingid" value="'.$building->getId().'" />
	Neue Id: <input type="text" name="newid" size="10" />
	<input type="submit" value="OK" /></form><br /><br />
	<a href="building.php">Zurück</a>';

?>
