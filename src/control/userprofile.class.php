<?php
class UserProfileApp extends gameapp {

	private $default_tpl = "html/userprofile.xhtml";

	function __construct() {
		parent::__construct($this->default_tpl,"/ Siedlerprofil");
		$this->addNavigationPart(new Tuple("userprofile.php?uid=".$this->getProfile()->getId(),"Siedlerprofil"));

		$this->registerProfileView();

		$this->render($this);
	}

	private $profile = NULL;

	function getProfile() {
		if ($this->profile === NULL) {
			$this->profile = new User(request::getIntFatal('uid'));
		}
		return $this->profile;
	}

	function registerProfileView() {
		if ($this->getProfile()->getId() == currentUser()->getId()) {
			return;
		}
		if (UserProfileVisitors::hasVisit($this->getProfile()->getId(),currentUser()->getId())) {
			return;
		}
		UserProfileVisitors::registerVisit($this->getProfile()->getId(),currentUser()->getId());
	}

	private $plots = NULL;

	function getRPGPlots() {
		if ($this->plots === NULL) {
			$this->plots = RPGPlotMember::getPlotsByUser($this->getProfile()->getId());
		}
		return $this->plots;
	}
}
?>
