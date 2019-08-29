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

	private $topics = NULL;

	function getTopicCount() {
		if ($this->topics === NULL) {
			$this->topics = AllianceTopic::countInstances('board_id='.$this->getId());
		}
		return $this->topics;
	}

	private $posts = NULL;

	function getPostCount() {
		if ($this->posts === NULL) {
			$this->posts = AlliancePost::countInstances('board_id='.$this->getId());
		}
		return $this->posts;
	}

	private $latestpost = NULL;

	function getLatestPost() {
		if ($this->latestpost === NULL) {
			$this->latestpost = AlliancePost::getLatestObjectBy('alliance_id='.$this->getAllianceId().' AND board_id='.$this->getId());
		}
		return $this->latestpost;
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

	static function getByAlliance($boardId,$allianceId) {
		$result = DB()->query("SELECT * FROM ".self::getTable()." WHERE id=".$boardId." AND alliance_id=".$allianceId." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($boardId);
		}
		return new AllianceBoardData($result);
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

	private $posts = NULL;

	function getPostCount() {
		if ($this->posts === NULL) {
			$this->posts = AlliancePost::countInstances('topic_id='.$this->getId());
		}
		return $this->posts;
	}

	private $latestpost = NULL;

	function getLatestPost() {
		if ($this->latestpost === NULL) {
			$this->latestpost = AlliancePost::getLatestObjectBy('alliance_id='.$this->getAllianceId().' AND topic_id='.$this->getId());
		}
		return $this->latestpost;
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

	private $postings = NULL;

	function getPostings($mark=FALSE) {
		if ($this->postings === NULL) {
			if ($mark !== FALSE) {
				$limit = ' LIMIT '.$mark.','. Topic::ALLIANCEBOARDLIMITER;
			} else {
				$limit = '';
			}
			$this->postings = AlliancePost::getList("topic_id=".$this->getId()." ORDER BY date ASC".$limit);
		}
		return $this->postings;
	}

	function getLastPostDate() {
		return $this->data['last_post_date'];
	}

	function setLastPostDate($value) {
		$this->data['last_post_date'] = $value;
		$this->addUpdateField('last_post_date','getLastPostDate');
	}

	function getLastPostDateDisplay() {
		return date("d.m.Y H:i",$this->getLastPostDate());
	}

	/**
	 */
	public function deepDelete() { #{{{
		foreach ($this->getPostings() as $key => $obj) {
			$obj->deepDelete();
		}
		$this->deleteFromDatabase();
	} # }}}

	private $pages = NULL;

	/**
	 */
	public function getPages() { #{{{
		if ($this->pages === NULL) {
			if ($this->getPostCount() <= Topic::ALLIANCEBOARDLIMITER) {
				return FALSE;
			}
			for ($i=1;$i<=ceil($this->getPostCount()/Topic::ALLIANCEBOARDLIMITER);$i++) {
				$this->pages[$i] = ($i-1)*Topic::ALLIANCEBOARDLIMITER;
			}
		}
		return $this->pages;
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

	static function getByAlliance($topicId,$allianceId) {
		$result = DB()->query("SELECT * FROM ".self::getTable()." WHERE id=".$topicId." AND alliance_id=".$allianceId." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new AllianceTopicData($result);
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
		$this->data['text'] = encodeString($value);
		$this->addUpdateField('text','getText');
	}

	function getText() {
		return $this->data['text'];
	}

	function getTextDecoded() {
		return stripslashes(BBCode()->render(decodeString($this->getText())));
	}

	function getTextDecodedRaw() {
		return stripslashes(BBCode()->render(decodeString($this->getText(),FALSE)));
	}

	function getTextParsed() {
		return nl2br($this->getTextDecoded());
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

	private $user = NULL;

	function getUser() {
		if ($this->user === NULL) {
			$this->user = new User($this->getUserId());
		}
		return $this->user;
	}

	private $topic = NULL;

	function getTopic() {
		if ($this->topic === NULL) {
			$this->topic = new AllianceTopic($this->getTopicId());
		}
		return $this->topic;
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

	static function getByAlliance($postId,$allianceId) {
		$result = DB()->query("SELECT * FROM ".self::getTable()." WHERE id=".$postId." AND alliance_id=".$allianceId." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new AlliancePostData($result);
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
