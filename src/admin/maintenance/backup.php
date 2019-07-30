<?php
class Backup {

	static public function handle() {
		self::cleanup();
		DB()->backup();
	}

	static private function cleanup() {
		$dir = dir(BACKUP_DIR);
		while($file = $dir->read()) {
			if (is_file(BACKUP_DIR.$file) && filectime(BACKUP_DIR.$file) < time()-BACKUP_PURGE) {
				unlink(BACKUP_DIR.$file);
			}
		}
		$dir->close();

	}
}
Backup::handle();
?>
