<?php

class NotesData extends BaseTable {

	protected $tablename = 'stu_notes';
	const tablename = 'stu_notes';

	function __construct(&$data=array()) {
		$this->data = $data;
	}

	public function getUserId() {
		return $this->data['user_id'];
	}

	public function setUserId($value) {
		$this->setFieldValue('user_id',$value,'getUserId');
	}

	public function getDate() {
		return $this->data['date'];
	}

	public function setDate($value) {
		$this->setFieldValue('date',$value,'getDate');
	}

	public function getTitle() {
		return $this->data['title'];
	}

	public function setTitle($value) {
		$this->setFieldValue('title',encodeString($value),'getTitle');
	}

	public function getTitleDecoded() {
		return decodeString($this->getTitle());
	}

	public function getTitleDecodedRaw() {
		return decodeString($this->getTitle(),FALSE);
	}

	public function getText() {
		return $this->data['text'];
	}

	public function setText($value) {
		$this->setFieldValue('text',encodeString($value),'getText');
	}

	public function getTextDecoded() {
		return decodeString($this->getText());
	}

	public function getTextDecodedRaw() {
		return decodeString($this->getText(),FALSE);
	}

	public function getDateFormatted() {
		return parseDateTime($this->getDate());
	}

	public function forceOwnedByCurrentUser() {
		if ($this->getUserId() != currentUser()->getId()) {
			throw new AccessViolation;
		}
	}
}
class Notes extends NotesData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($crewId);
		}
		return parent::__construct($result);
	}

	static function getListByUser($userId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE user_id=".$userId." ORDER BY date DESC");
		return self::_getList($result,'NotesData');
	}

	/**
	 */
	static function truncate($sql='') { #{{{
		DB()->query('DELETE FROM '.self::tablename.' '.$sql);
	} # }}}

}
?>
