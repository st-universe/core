<?php

include_once(__DIR__.'/../../inc/config.inc.php');

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class SystemNameGenerator { #{{{

	/**
	 */
	static function handle() { #{{{
		$list = StarSystem::getObjectsBy("WHERE name='' OR name='John Doe'");
		foreach ($list as $key => $system) {
			$obj = SystemNameList::findObject('ORDER BY RAND()');
			$system->setName($obj->getName());
			$system->save();
			$obj->deleteFromDatabase();
		}
	} # }}}

} #}}}

SystemNameGenerator::handle();
?>
