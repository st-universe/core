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
class RumpCatRoleCrewData extends BaseTable { #{{{

	protected $tablename = 'stu_rumps_cat_role_crew';
	const tablename = 'stu_rumps_cat_role_crew';

	/**
	 */
	function __construct(&$data=array()) { #{{{
		$this->data = $data;
	} # }}}

	/**
	 */
	public function setRumpCategoryId($value) { # {{{
		$this->setFieldValue('rumps_category_id',$value,'getRumpCategoryId');
	} # }}}

	/**
	 */
	public function getRumpCategoryId() { # {{{
		return $this->data['rumps_category_id'];
	} # }}}

	/**
	 */
	public function setRumpRoleId($value) { # {{{
		$this->setFieldValue('rump_role_id',$value,'getRumpRoleId');
	} # }}}

	/**
	 */
	public function getRumpRoleId() { # {{{
		return $this->data['rump_role_id'];
	} # }}}

	/**
	 */
	public function setJob1Crew($value) { # {{{
		$this->setFieldValue('job_1_crew',$value,'getJob1Crew');
	} # }}}

	/**
	 */
	public function getJob1Crew() { # {{{
		return $this->data['job_1_crew'];
	} # }}}

	/**
	 */
	public function setJob2Crew($value) { # {{{
		$this->setFieldValue('job_2_crew',$value,'getJob2Crew');
	} # }}}

	/**
	 */
	public function getJob2Crew() { # {{{
		return $this->data['job_2_crew'];
	} # }}}

	/**
	 */
	public function setJob3Crew($value) { # {{{
		$this->setFieldValue('job_3_crew',$value,'getJob3Crew');
	} # }}}

	/**
	 */
	public function getJob3Crew() { # {{{
		return $this->data['job_3_crew'];
	} # }}}

	/**
	 */
	public function setJob4Crew($value) { # {{{
		$this->setFieldValue('job_4_crew',$value,'getJob4Crew');
	} # }}}

	/**
	 */
	public function getJob4Crew() { # {{{
		return $this->data['job_4_crew'];
	} # }}}

	/**
	 */
	public function setJob5Crew($value) { # {{{
		$this->setFieldValue('job_5_crew',$value,'getJob5Crew');
	} # }}}

	/**
	 */
	public function getJob5Crew() { # {{{
		return $this->data['job_5_crew'];
	} # }}}

	/**
	 */
	public function setJob6Crew($value) { # {{{
		$this->setFieldValue('job_6_crew',$value,'getJob6Crew');
	} # }}}

	/**
	 */
	public function getJob6Crew() { # {{{
		return $this->data['job_6_crew'];
	} # }}}

	/**
	 */
	public function setJob6Crew10p($value) { # {{{
		$this->setFieldValue('job_6_crew_10p',$value,'getJob6Crew10p');
	} # }}}

	/**
	 */
	public function getJob6Crew10p() { # {{{
		return $this->data['job_6_crew_10p'];
	} # }}}

	/**
	 */
	public function setJob6Crew20p($value) { # {{{
		$this->setFieldValue('job_6_crew_20p',$value,'getJob6Crew20p');
	} # }}}

	/**
	 */
	public function getJob6Crew20p() { # {{{
		return $this->data['job_6_crew_20p'];
	} # }}}

	/**
	 */
	public function getJob6Crew100() { #{{{
		return $this->getJob6Crew();
	} # }}}

	/**
	 */
	public function getJob6Crew110() { #{{{
		return $this->getJob6Crew10P();
	} # }}}

	/**
	 */
	public function getJob6Crew120() { #{{{
		return $this->getJob6Crew20p();
	} # }}}

	/**
	 */
	public function setJob7Crew($value) { # {{{
		$this->setFieldValue('job_7_crew',$value,'getJob7Crew');
	} # }}}

	/**
	 */
	public function getJob7Crew() { # {{{
		return $this->data['job_7_crew'];
	} # }}}

} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class RumpCatRoleCrew extends RumpCatRoleCrewData { #{{{

	/**
	 */
	function __construct($id) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);	
		if ($result == 0) {
			throw new ObjectNotFoundException($ship_id);
		}
		parent::__construct($result);
	} # }}}

	/**
	 */
	static function getByRumpCatRole($category_id,$role_id) { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' WHERE rump_category_id='.$category_id.' AND rump_role_id='.$role_id.' LIMIT 1',4);
		return new RumpCatRoleCrewData($result);
	} # }}}

} #}}}

?>
