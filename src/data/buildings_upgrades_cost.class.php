<?php

/*
 * Copyright 2011 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 */


/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 */
class BuildingsUpgradesCost extends BuildingsUpgradesCost_Table { # {{{

	/**
	 */
	public function _create() {
		return new BuildingsUpgradesCost;
	}

	/**
	 */
	public function getGood() { #{{{
		return ResourceCache()->getGood($this->getGoodId());
	} # }}}

} # }}}

?>
