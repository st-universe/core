<?php

/*
 *
 * Copyright 2010 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 *
 */

/* $Id:$ */

class KnCommentData extends BaseTable {

	protected $tablename = 'stu_kn_comments';
	const tablename = 'stu_kn_comments';

	function __construct(&$data=array()) {
		$this->data = $data;
	}

	/**
	 */
	public function setPostId($value) { # {{{
		$this->setFieldValue('post_id',$value,'getPostId');
	} # }}}

	/**
	 */
	public function getPostId() { # {{{
		return $this->data['post_id'];
	} # }}}

	/**
	 */
	public function setUserId($value) { # {{{
		$this->setFieldValue('user_id',$value,'getUserId');
	} # }}}

	/**
	 */
	public function getUserId() { # {{{
		return $this->data['user_id'];
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
	public function setText($value) { # {{{
		$this->setFieldValue('text',$value,'getText');
	} # }}}

	/**
	 */
	public function getText() { # {{{
		return $this->data['text'];
	} # }}}

	/**
	 */
	public function setDate($value) { # {{{
		$this->setFieldValue('date',$value,'getDate');
	} # }}}

	/**
	 */
	public function getDate() { # {{{
		return $this->data['date'];
	} # }}}

	/**
	 */
	public function getDisplayUserName() { #{{{
		if ($this->getUserName()) {
			return $this->getUserName();
		}
		return ResourceCache()->getObject('user',$this->getUserId())->getName();
	} # }}}

	private $posting = NULL;

	/**
	 */
	public function getPosting() { #{{{
		if ($this->posting === NULL) {
			$this->posting = new KNPosting($this->getPostId());
		}
		return $this->posting;
	} # }}}

	public function getUserAvatarPath() {
		if ($this->getUserName()) {
			return FALSE;
		}
		return ResourceCache()->getObject('user',$this->getUserId())->getFullAvatarPath();
	}
}
class KnComment extends KnCommentData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	/**
	 */
	static function countInstances($where) { #{{{
		return parent::getCount(self::tablename,$where);
	} # }}}

	/**
	 */
	static function getByPostingId($postingId) { #{{{
		return parent::_getList(DB()->query("SELECT * FROM ".self::tablename." WHERE post_id=".intval($postingId)." ORDER BY id DESC"),"KnCommentData");
	} # }}}

	/**
	 */
	static function truncate($sql='') { #{{{
		DB()->query('DELETE FROM '.self::tablename.' '.$sql);
	} # }}}

}
?>
