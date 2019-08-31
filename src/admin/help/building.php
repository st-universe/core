<?php

include_once(__DIR__.'/../../inc/config.inc.php');

$result = Building::getObjectsBy('ORDER BY id');
foreach ($result as $key => $obj) {
	echo $obj->getName().': <a href="buildingfieldassigner.php?buildingid='.$obj->getId().'">Feldzuweisung</a> - <a href="buildingcosts.php?buildingid='.$obj->getId().'">Baukosten</a><br />';
}
?>
