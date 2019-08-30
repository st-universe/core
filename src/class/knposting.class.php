<?php
class KNPostingData extends BaseTable {

	const tablename = 'stu_kn';
	protected $tablename = 'stu_kn';
	
	private $user = NULL;

	function __construct($data=NULL) {
		if ($data === NULL) {
			$data = array("titel" => "","text" => "");
		}
		$this->data = $data;
	}

	function getId() {
		return $this->data['id'];
	}

	function getUser() {
		if ($this->user === NULL) {
			$this->user = new User($this->data['user_id']);
		}
		return $this->user;
	}

	function getUserId() {
		return $this->data['user_id'];
	}

	function setUserId($value) {
		$this->data['user_id'] = $value;
		$this->addUpdateField('user_id','getUserId');
	}

	public function hasUser() {
		return $this->getUserId() > 0;
	}

	function hasTitle() {
		return $this->data['titel'] !== NULL && $this->data['titel'] != '';
	}

	function getTitle() {
		return $this->data['titel'];
	}

	function setTitle($value) {
		$this->data['titel'] = $value;
		$this->addUpdateField('titel','getTitle');
	}

	function getText() {
		return $this->data['text'];
	}

	function setText($value) {
		$this->data['text'] = $value;
		$this->addUpdateField('text','getText');
	}

	function getDate() {
		return $this->data['date'];
	}

	function setDate($value) {
		$this->data['date'] = $value;
		$this->addUpdateField('date','getDate');
	}

	function getEditDate() {
		return $this->data['lastedit'];
	}

	function setEditDate($value) {
		$this->data['lastedit'] = $value;
		$this->addUpdateField('lastedit','getEditDate');
	}

	function hasEdit() {
		return $this->getEditDate()>0;
	}

	function isEditAble() {
		return $this->getDate()>time()-600 && $this->getUserId() == currentUser()->getId();
	}

	function getPlotId() {
		return $this->data['plot_id'];
	}

	function setPlotId($value) {
		$this->data['plot_id'] = $value;
		$this->addUpdateField('plot_id','getPlotId');
	}

	function hasPlot() {
		return $this->getPlotId() > 0;
	}

	private $rpgplot = NULL;

	function getRPGPlot() {
		if ($this->rpgplot === NULL) {
			$this->rpgplot = new RPGPlot($this->getPlotId());
		}
		return $this->rpgplot;
	}

	private $commentCount = NULL;
	
	/**
	 */
	public function getCommentCount() { #{{{
		if ($this->commentCount === NULL) {
			$this->commentCount = KnComment::countInstances('post_id='.$this->getId());
		}
		return $this->commentCount;	
	} # }}}

	private $comments = NULL;

	/**
	 */
	public function getComments() { #{{{
		if ($this->comments === NULL) {
			$this->comments = KnComment::getByPostingId($this->getId());
		}
		return $this->comments;
	} # }}}

	/**
	 */
	public function currentUserMayDeleteComment() { #{{{
		return currentUser()->getId() == $this->getUserId();
	} # }}}

	/**
	 */
	public function displayUserLinks() { #{{{
		return $this->hasUser() && !$this->getUser()->isCurrentUser();
	} # }}}

	/**
	 */
	public function deleteAuthor() { #{{{
		$this->setUserName($this->getUser()->getName());
		$this->setUserId(0);
		$this->save();
	} # }}}

	/**
	 */
	public function setUserName($value) { # {{{
		$this->setFieldValue('username',$value,'getUserName');
	} # }}}

	/**
	 */
	public function getUserName() { # {{{
		return $this->data['username'];
	} # }}}

	/**
	 */
	public function isNewerThanMark() { #{{{
		return $this->getId() > currentUser()->getKNMark();
	} # }}}

	private $setKNMark = FALSE;

	/**
	 */
	public function setSetKNMark($value) { #{{{
		$this->setKNMark = $value;
	} # }}}

	/**
	 */
	public function getSetKNMark() { #{{{
		return $this->setKNMark;
	} # }}}

}
class KNPosting extends KNPostingData {
	
	function __construct($id=0) {
		$data = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".intval($id)." LIMIT 1",4);
		if ($data == 0) {
			new ObjectNotFoundException($id);
		}
		parent::__construct($data);
	}

	static function countInstances($qry) {
		return DB()->query("SELECT COUNT(id) FROM ".self::tablename." WHERE ".$qry,1);
	}

	static function getByPlotId($plotId) {
		$ret = array();
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE plot_id=".$plotId." ORDER BY id DESC");
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new KNPostingData($data);
		}
		return $ret;
	}

	/**
	 */
	static function getBy($sql) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$sql);
		return self::_getList($result,'KnPostingData');
	} # }}}

}
?>
