<?php

include_once(__DIR__.'/../../inc/config.inc.php');

DB()->beginTransaction();

$result = MapField::getListBy('WHERE field_id=99');
foreach ($result as $key => $obj) {
	$system = generate(getSystemType());
	$system->setCX($obj->getCX());
	$system->setCY($obj->getCY());
	$system->save();
	$field = MapFieldType::getFieldByType($system->getType());
	$obj->setFieldId($field->getId());
	$obj->save();
}

DB()->commitTransaction();
