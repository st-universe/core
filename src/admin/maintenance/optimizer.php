<?php

include_once(__DIR__.'/../../inc/config.inc.php');

class Optimizer {

	static public function handle() {
		DB()->optimize();
	}
}
Optimizer::handle();
