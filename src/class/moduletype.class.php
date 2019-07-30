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
class ModuleType { #{{{

	/**
	 */
	static function isMandatory($type) { #{{{
		switch($type) {
			case MODULE_TYPE_HULL:
			case MODULE_TYPE_SHIELDS:
			case MODULE_TYPE_EPS:
			case MODULE_TYPE_IMPULSEDRIVE:
			case MODULE_TYPE_WARPCORE:
			case MODULE_TYPE_COMPUTER:
			case MODULE_TYPE_SENSORS:
				return TRUE;
			default:
				return FALSE;
		}
	} # }}}

	/**
	 */
	static function getDescription($type) { #{{{
		switch ($type) {
			case MODULE_TYPE_HULL:
				return _("Hülle");
			case MODULE_TYPE_SHIELDS:
				return _("Schilde");
			case MODULE_TYPE_EPS:
				return _("EPS-Leitungen");
			case MODULE_TYPE_IMPULSEDRIVE:
				return _("Antrieb");
			case MODULE_TYPE_WARPCORE:
				return _("Reaktor");
			case MODULE_TYPE_COMPUTER:
				return _("Computer");
			case MODULE_TYPE_PHASER:
				return _("Energiewaffe");
			case MODULE_TYPE_TORPEDO:
				return _("Torpedobank");
			case MODULE_TYPE_SPECIAL:
				return _("Spezial");
			case MODULE_TYPE_SENSORS:
				return _("Sensoren");
		}
	} # }}}

} #}}}


?>
