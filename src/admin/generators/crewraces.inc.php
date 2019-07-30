<?php
include_once(__DIR__.'/../../inc/config.inc.php');

class CrewRacesGenerator extends DefaultGenerator {

	function __construct() {
		parent::__construct();
		$this->writeSuffix();
	}

	protected $file = 'crewraces.inc.php';

	protected function handle() {
		$result = DB()->query("SELECT * FROM stu_crew_race");
		while($data = mysqli_fetch_assoc($result)) {
			$this->write("define('CREW_RACE_".$data['define']."',".$data['id'].");");
		}
	}
}

new CrewRacesGenerator;
?>
