<?php
class RPGPlotData extends BaseTable {
	
	protected $tablename = 'stu_plots';
	const tablename = 'stu_plots';

	function __construct($data=array()) {
		$this->data = $data;
	}

	function getId() {
		return $this->data['id'];
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function isNew() {
		return !$this->data['id'] || $this->data['id'] == 0; 
	}

	function getUserId() {
		return $this->data['user_id'];
	}

	function setUserId($value) {
		$this->data['user_id'] = $value;
		$this->addUpdateField('user_id','getUserId');
	}

	function getTitle() {
		return $this->data['title'];
	}

	function getTitleDecoded() {
		return stripslashes(decodeString($this->getTitle()));
	}

	function getTitleDecodedRaw() {
		return stripslashes(decodeString($this->getTitle(),FALSE));
	}

	function setTitle($value) {
		$this->data['title'] = encodeString(strip_tags($value));
		$this->addUpdateField('title','getTitle');
	}

	function getDescription() {
		return $this->data['description'];
	}

	function getDescriptionDecodedRaw() {
		return stripslashes(decodeString($this->getDescription(),FALSE));
	}

	function getDescriptionDecoded() {
		return nl2br(stripslashes(decodeString($this->getDescription())));
	}

	function setDescription($value) {
		$this->data['description'] = encodeString(strip_tags($value));
		$this->addUpdateField('description','getDescription');
	}

	function setStartDate($value) {
		$this->data['start_date'] = $value;
		$this->addUpdateField('start_date','getStartDate');
	}

	function getStartDate() {
		return $this->data['start_date'];
	}

	function getStartDateDisplay() {
		return date("d.m.Y H:i",$this->getStartDate());
	}

	function getEndDate() {
		return $this->data['end_date'];
	}

	function getEndDateDisplay() {
		return date("d.m.Y H:i",$this->getEndDate());
	}

	function setEndDate($value) {
		$this->data['end_date'] = $value;
		$this->addUpdateField('end_date','getEndDate');
	}

	function isActive() {
		return $this->getEndDate() == 0;
	}

	private $membercount = NULL;

	function getMemberCount() {
		if ($this->membercount === NULL) {
			$this->membercount = RPGPlotMember::countInstances("plot_id=".$this->getId());
		}
		return $this->membercount;
	}

	private $postingcount = NULL;

	function getPostingCount() {
		if ($this->postingcount === NULL) {
			$this->postingcount = KNPosting::countInstances("plot_id=".$this->getId());
		}
		return $this->postingcount;
	}

	private $postings = NULL;

	function getPostings() {
		if ($this->postings === NULL) {
			$this->postings = KNPosting::getByPlotId($this->getId());
		}
		return $this->postings;
	}

	private $members = NULL;

	function getMembers() {
		if ($this->members === NULL) {
			$this->members = RPGPlot::getMembersByPlot($this->getId());
		}
		return $this->members;
	}

	function ownedByCurrentUser() {
		return currentUser()->getId() == $this->getUserId();
	}

	function mayEdit() {
		// may be usefull for privileges
		return $this->ownedByCurrentUser();
	}

	/**
	 */
	public function deleteOwner() { #{{{
		RPGPlotMember::findObject('WHERE user_id='.$this->getUserId().' AND plot_id='.$this->getId())->deleteFromDatabase();
		if ($this->getMembers()) {
			$member = current($this->getMembers());
			$this->setUserId($member->getUserId());
			$this->save();
			return;
		}
		$this->setUserId(USER_NOONE);
		$this->save();
	} # }}}

}
class RPGPlot extends RPGPlotData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".parent::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getById($id) {
		$result = DB()->query("SELECT * FROM ".parent::tablename." WHERE id=".intval($id)." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new RPGPlotData($result);
	}

	static function getObjectsBy($qry) {
		$result = DB()->query("SELECT * FROM ".parent::tablename." ".$qry);
		$ret = array();
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new RPGPlotData($data);
		}
		return $ret;
	}

	static function getMembersByPlot($plotId) {
		$ret = array();
		$result = DB()->query("SELECT COUNT(b.id) as count,a.user_id FROM stu_plots_members as a
			LEFT JOIN stu_kn as b USING(user_id,plot_id) WHERE a.plot_id=".$plotId." GROUP BY a.user_id");
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = array("user" => new User($data['user_id']),"count" => $data['count']);
		}
		return $ret;
	}

	static function checkUserPlot($userId,$plotId) {
		return DB()->query("SELECT id FROM stu_plots_members WHERE user_id=".$userId." AND plot_id=".$plotId,1);
	}

	static function addPlotMember($userId,$plotId) {
		DB()->query("INSERT INTO stu_plots_members (user_id,plot_id) VALUES ('".$userId."','".$plotId."')");
	}

	static function delPlotMember($userId,$plotId) {
		DB()->query("DELETE FROM stu_plots_members WHERE plot_id=".$plotId." AND user_id=".$userId);
	}

}
?>
