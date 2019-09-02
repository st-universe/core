<?php

use Stu\Module\Alliance\View\Topic\Topic;

class AllianceBoardData extends BaseTable {

	const TABLENAME = 'stu_alliance_boards';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function getTable() {
		return self::TABLENAME;
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getId() {
		return $this->data['id'];
	}

	function getName() {
		return $this->data['name'];
	}

	function setName($value) {
		$this->data['name'] = strip_tags($value);
		$this->addUpdateField('name','getName');
	}

	function getAllianceId() {
		return $this->data['alliance_id'];
	}

	function setAllianceId($value) {
		$this->data['alliance_id'] = $value;
		$this->addUpdateField('alliance_id','getAllianceId');
	}

	function getTopicCount() {
		return AllianceTopic::countInstances('board_id='.$this->getId());
	}

	function getPostCount() {
		return AlliancePost::countInstances('board_id='.$this->getId());
	}

	function getLatestPost() {
		return AlliancePost::getLatestObjectBy('alliance_id='.$this->getAllianceId().' AND board_id='.$this->getId());
	}

	/**
	 */
	public function getTopics() { #{{{
		return AllianceTopic::getList('board_id='.$this->getId());
	} # }}}

	/**
	 */
	public function deepDelete() { #{{{
		foreach ($this->getTopics() as $key => $obj) {
			$obj->deepDelete();
		}
		$this->deleteFromDatabase();
	} # }}}
}
class AllianceBoard extends AllianceBoardData {

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".self::getTable()." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getList($sql) {
		$ret = array();
		$result = DB()->query("SELECT * FROM ".self::getTable()." WHERE ".$sql);
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[] = new AllianceBoardData($data);
		}
		return $ret;
	}

	static function getListByAlliance($allianceId) {
		return self::getList('alliance_id='.$allianceId);
	}
}

class AllianceTopicData extends BaseTable {

	const TABLENAME = 'stu_alliance_topics';
	
	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function getTable() {
		return self::TABLENAME;
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getId() {
		return $this->data['id'];
	}

	function getName() {
		return $this->data['name'];
	}

	function setName($value) {
		$this->data['name'] = strip_tags($value);
		$this->addUpdateField('name','getName');
	}

	function getAllianceId() {
		return $this->data['alliance_id'];
	}

	function setAllianceId($value) {
		$this->data['alliance_id'] = $value;
		$this->addUpdateField('alliance_id','getAllianceId');
	}

	function getPostCount() {
		return AlliancePost::countInstances('topic_id='.$this->getId());
	}

	function getLatestPost() {
		return AlliancePost::getLatestObjectBy('alliance_id='.$this->getAllianceId().' AND topic_id='.$this->getId());
	}

	function setBoardId($value) {
		$this->data['board_id'] = $value;
		$this->addUpdateField('board_id','getBoardId');
	}

	function getBoardId() {
		return $this->data['board_id'];
	}

	function setUserId($value) {
		$this->data['user_id'] = $value;
		$this->addUpdateField('user_id','getUserId');
	}

	function getUserId() {
		return $this->data['user_id'];
	}

	function getPostings($mark=FALSE) {
		if ($mark !== FALSE) {
			$limit = ' LIMIT '.$mark.','. Topic::ALLIANCEBOARDLIMITER;
		} else {
			$limit = '';
		}
		return AlliancePost::getList("topic_id=".$this->getId()." ORDER BY date ASC".$limit);
	}

	function getLastPostDate() {
		return $this->data['last_post_date'];
	}

	function setLastPostDate($value) {
		$this->data['last_post_date'] = $value;
		$this->addUpdateField('last_post_date','getLastPostDate');
	}

	/**
	 */
	public function deepDelete() { #{{{
		foreach ($this->getPostings() as $key => $obj) {
			$obj->deepDelete();
		}
		$this->deleteFromDatabase();
	} # }}}

	public function getPages() { #{{{
		$postCount = $this->getPostCount();

		if ($postCount <= Topic::ALLIANCEBOARDLIMITER) {
			return null;
		}

		$pages = [];
		for ($i = 1; $i <= ceil($postCount / Topic::ALLIANCEBOARDLIMITER); $i++) {
			$pages[$i] = ($i-1) * Topic::ALLIANCEBOARDLIMITER;
		}
		return $pages;
	} # }}}

	/**
	 */
	public function setSticky($value) { # {{{
		$this->setFieldValue('sticky',$value,'getSticky');
	} # }}}

	/**
	 */
	public function getSticky() { # {{{
		return $this->data['sticky'];
	} # }}}

}
class AllianceTopic extends AllianceTopicData {

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".self::getTable()." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getList($sql) {
		$ret = array();
		$result = DB()->query("SELECT * FROM ".self::getTable()." WHERE ".$sql);
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[] = new AllianceTopicData($data);
		}
		return $ret;
	}

	static function countInstances($sql) {
		return DB()->query("SELECT COUNT(*) FROM ".self::getTable()." WHERE ".$sql." LIMIT 1",1);
	}

	static function truncate($sql) {
		DB()->query("DELETE FROM ".self::getTable()." WHERE ".$sql);
	}

	static function getLatestTopics($allianceId) {
		return self::getList('alliance_id='.$allianceId.' ORDER BY last_post_date DESC LIMIT 3');	
	}

}

class AlliancePostData extends BaseTable {

	const TABLENAME = 'stu_alliance_posts';
	
	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function getTable() {
		return self::TABLENAME;
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getId() {
		return $this->data['id'];
	}

	function getName() {
		return $this->data['name'];
	}

	function setName($value) {
		$this->data['name'] = strip_tags($value);
		$this->addUpdateField('name','getName');
	}

	function getAllianceId() {
		return $this->data['alliance_id'];
	}

	function setAllianceId($value) {
		$this->data['alliance_id'] = $value;
		$this->addUpdateField('alliance_id','getAllianceId');
	}

	function setTopicId($value) {
		$this->data['topic_id'] = $value;
		$this->addUpdateField('topic_id','getTopicId');
	}

	function getTopicId() {
		return $this->data['topic_id'];
	}

	function setBoardId($value) {
		$this->data['board_id'] = $value;
		$this->addUpdateField('board_id','getBoardId');
	}

	function getBoardId() {
		return $this->data['board_id'];
	}

	function setText($value) {
		$this->data['text'] = $value;
		$this->addUpdateField('text','getText');
	}

	function getText() {
		return $this->data['text'];
	}

	function setDate($value) {
		$this->data['date'] = $value;
		$this->addUpdateField('date','getDate');
	}

	function getDate() {
		return $this->data['date'];
	}

	function setUserId($value) {
		$this->data['user_id'] = $value;
		$this->addUpdateField('user_id','getUserId');
	}

	function getUserId() {
		return $this->data['user_id'];
	}

	function getUser() {
		return new User($this->getUserId());
	}

	function getTopic() {
		return new AllianceTopic($this->getTopicId());
	}

	/**
	 */
	public function deepDelete() { #{{{
		$this->deleteFromDatabase();
	} # }}}
}
class AlliancePost extends AlliancePostData {

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".self::getTable()." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getList($sql) {
		$ret = array();
		$result = DB()->query("SELECT * FROM ".self::getTable()." WHERE ".$sql);
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[] = new AlliancePostData($data);
		}
		return $ret;
	}

	static function countInstances($sql) {
		return DB()->query("SELECT COUNT(*) FROM ".self::getTable()." WHERE ".$sql." LIMIT 1",1);
	}

	static function getLatestObjectBy($sql) {
		$result = DB()->query("SELECT * FROM ".self::getTable()." WHERE ".$sql." ORDER BY date DESC LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new AlliancePostData($result);
	}

	static function truncate($sql) {
		DB()->query("DELETE FROM ".self::getTable()." WHERE ".$sql);
	}

}
?>
