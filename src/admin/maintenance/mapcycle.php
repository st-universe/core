<?php

include_once(__DIR__.'/../../inc/config.inc.php');

class Mapcycle {

	static public function handle() {
		$fieldcount = Mapfield::countInstances(); 
		$list = User::getListBy("WHERE maptype=".MAPTYPE_INSERT);
		foreach ($list as $key => $user) {
			if (DB()->query("SELECT COUNT(*) FROM stu_user_map WHERE user_id=".$user->getId()) >= $fieldcount) {
				self::cycle($user);
			}
		}
	}

	static private function cycle(&$user) {
		$user->setMapType(MAPTYPE_DELETE);
		$user->save();
		
		$fields = DB()->query("SELECT cx,cy,id FROM stu_map WHERE id NOT IN (SELECT map_id FROM stu_user_map WHERE user_id=".$user->getId().")");
		DB()->query("DELETE FROM stu_user_map WHERE user_id=".$user->getId());
		while ($data=mysqli_fetch_assoc($fields)) {
			DB()->query("INSERT INTO stu_user_map (cx,cy,user_id,map_id) VALUES ('".$data['cx']."','".$data['cy']."','".$user->getId()."','".$data['id']."')");
		}

	}
}
Mapcycle::handle();
?>
