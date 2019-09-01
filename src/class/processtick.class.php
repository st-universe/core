<?php

class ProcessTick {

	static function finishBuildProcesses() {
		$result = Colfields::getListBy("aktiv>1 AND aktiv<".time());
		foreach ($result as $key => $field) {
			$field->setActive(0);
			if ($field->getBuilding()->isActivateAble() && $field->getColony()->getWorkless() >= $field->getBuilding()->getWorkers()) {
				$field->setActive(1);
				$field->getColony()->upperWorkers($field->getBuilding()->getWorkers());
				$field->getColony()->lowerWorkless($field->getBuilding()->getWorkers());
				$field->getColony()->upperMaxBev($field->getBuilding()->getHousing());
			}
			$field->getColony()->upperMaxStorage($field->getBuilding()->getStorage());
			$field->getColony()->upperMaxEps($field->getBuilding()->getEpsStorage());
			$field->getColony()->save();
			$field->setIntegrity($field->getBuilding()->getIntegrity());
			$field->save();
			$txt = "Kolonie ".$field->getColony()->getNameWithoutMarkup().": ".$field->getBuilding()->getName()." auf Feld ".$field->getFieldId()." fertiggestellt";
			PM::sendPM(USER_NOONE,$field->getColony()->getUserId(),$txt,PM_SPECIAL_COLONY);
		}
	}

	static function finishTerraformingProcesses() {
		$result = FieldTerraforming::getFinishedJobs();
		foreach ($result as $key => $field) {
			/**
			 * @var FieldTerraformingData $field
			 */
			$field->getField()->setFieldType($field->getTerraforming()->getToFieldTypeId());
			$field->getField()->setTerraformingId(0);
			$field->getField()->save();
			$field->deleteFromDatabase();
			$txt = "Kolonie ".$field->getColony()->getNameWithoutMarkup().": ".$field->getTerraforming()->getDescription()." auf Feld ".$field->getField()->getFieldId()." abgeschlossen";
			PM::sendPM(USER_NOONE,$field->getColony()->getUserId(),$txt,PM_SPECIAL_COLONY);
		}
	}

	/**
	 */
	static function processShipQueue() { #{{{
		DB()->beginTransaction();
		$queue = ColonyShipQueue::getFinishedJobs();
		foreach ($queue as $key => $obj) {
			$colony = ResourceCache()->getObject('colony',$obj->getColonyId());
			$ship = Ship::createBy($obj->getUserId(),$obj->getRumpId(),$obj->getBuildplanId(),$colony);

			$obj->deleteFromDatabase();
			$txt = _("Auf der Kolonie ".$colony->getNameWithoutMarkup()." wurde ein Schiff der ".$ship->getRump()->getName()."-Klasse fertiggestellt");
			PM::sendPM(USER_NOONE,$colony->getUserId(),$txt,PM_SPECIAL_COLONY);
		}
		DB()->commitTransaction();
	} # }}}

	/**
	 */
	static function processShieldRegeneration() { #{{{
		DB()->beginTransaction();
		$time = strtotime(date("d.m.Y H:i",time()));
		$result = Ship::getObjectsBy('WHERE rumps_id NOT IN (SELECT id FROM stu_rumps WHERE category_id='.SHIP_CATEGORY_DEBRISFIELD.') AND schilde<max_schilde AND shield_regeneration_timer<='.($time - SHIELD_REGENERATION_TIME));
		foreach ($result as $key => $obj) {
			$obj->regenerateShields($time);
		}
		DB()->commitTransaction();
	} # }}}

}
?>
