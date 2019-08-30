<?php

/*
 *
 * Copyright 2010 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 *
 */

/* $Id:$ */

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class TradeShoutboxData extends BaseTable { #{{{

	const tablename = 'stu_trade_shoutbox';
	protected $tablename = 'stu_trade_shoutbox';

	/**
	 */
	function __construct(&$data=array()) { #{{{
		$this->data = $data;
	} # }}}

	/**
	 */
	public function setTradeNetworkId($value) { # {{{
		$this->setFieldValue('trade_network_id',$value,'getTradeNetworkId');
	} # }}}

	/**
	 */
	public function getTradeNetworkId() { # {{{
		return $this->data['trade_network_id'];
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
	public function setMessage($value) { # {{{
		$this->setFieldValue('message',$value,'getMessage');
	} # }}}

	/**
	 */
	public function getMessage() { # {{{
		return $this->data['message'];
	} # }}}

	/**
	 */
	public function getUser() { #{{{
		return ResourceCache()->getObject('user',$this->getUserId());
	} # }}}

} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class TradeShoutbox extends TradeShoutboxData { #{{{

	function __construct($id=0) { # {{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	} # }}}
	
	/**
	 */
	static function getByTradeNetworkId($tradeNetworkId) { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' WHERE trade_network_id='.intval($tradeNetworkId).' ORDER BY id');
		return self::_getList($result,'TradeShoutboxData');
	} # }}}

	/**
	 */
	static function deleteHistory($tradeNetworkId,$limit=30) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE trade_network_id=".intval($tradeNetworkId)." ORDER BY id DESC LIMIT ".$limit.",1",4);
		if ($result == 0) {
			return;
		}
		$obj = new TradeShoutboxData($result);
		DB()->query("DELETE FROM ".self::tablename." WHERE trade_network_id=".intval($tradeNetworkId)." AND id<=".$obj->getId());
	} # }}}
	
	/**
	 */
	static function truncate($sql='') { #{{{
		DB()->query('DELETE FROM '.self::tablename.' '.$sql);
	} # }}}

} #}}}


?>
