<?php

final class colonylist extends gameapp {

	private $default_tpl = "html/colonylist.xhtml";

	function __construct() {
		parent::__construct($this->default_tpl,"/ Kolonien");

		$this->addCallBack('B_GIVEUP_COLONY','giveupColony',TRUE);

		$this->addNavigationPart(new Tuple("colonylist.php","Kolonien"));
		$this->render($this);
	}

	function hasColonies() {
		return count($this->getColonyList()) != 0;
	}

	private $colonylist = NULL;

	function getColonyList() {
		if ($this->colonylist === NULL) {
			$this->colonylist = Colony::getListBy('user_id='.currentUser()->getId());
		}
		return $this->colonylist;
	}

	private $terraformings = NULL;

	function getTerraformingJobs() {
		if ($this->terraformings === NULL) {
			$this->terraformings = FieldTerraforming::getUnFinishedJobsByUser(currentUser()->getId());
		}
		return $this->terraformings;
	}

	private $buildings = NULL;

	function getBuildingJobs() {
		if ($this->buildings === NULL) {
			$this->buildings = Colfields::getUnFinishedBuildingJobsByUser(currentUser()->getId());
		}
		return $this->buildings;
	}

	function giveupColony() {
		$col = new UserColony(request::getIntFatal('id'));	
		$col->updateColonySurface();
		$col->setEps(0);
		$col->setMaxEps(0);
		$col->setMaxStorage(0);
		$col->setWorkers(0);
		$col->setWorkless(0);
		$col->setMaxBev(0);
		$col->setImmigrationState(1);
		$col->setPopulationLimit(0);
		$col->setUserId(USER_NOONE);
		$col->setName('');
		$col->save();

		ColStorage::truncate($col->getId());
		FieldTerraforming::truncate($col->getId());
		ColonyShipQueue::truncate('colony_id='.$col->getId());

		currentUser()->checkActivityLevel();

		$this->addInformation(_("Die Kolonie wurde aufgegeben"));
	}

}
