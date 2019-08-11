<?php

class db {
	private $dblink;
	private $querycount = 0;

	function __construct() {
		// @todo remove global, inject instead
		global $config;

		$this->dblink = mysqli_connect(
			$config->get('db.host'),
			$config->get('db.user'),
			$config->get('db.pass'),
			$config->get('db.database')
		);
		if (!$this->dblink) {
			new DBException(mysqli_error($this->dblink));
			return;
		}
		mysqli_query($this->dblink,"SET SESSION sql_mode = ''");
		mysqli_set_charset($this->dblink, 'utf8');
		if (mysqli_error($this->dblink)) {
			new DBException(mysqli_error($this->dblink));
			return;
		}
	}

	public function query($qry,$mode=0) {
		if (!$qry) {
			return;
		}
		$this->iterateQueryCount();
		$result = mysqli_query($this->dblink, $qry);
		if (mysqli_error($this->dblink)) {
			throw new DBException(mysqli_error($this->dblink),$qry);
		}
		if ($mode == 0) {
			return $result;
		}
		if ($mode == 5) {
			return @mysqli_insert_id($this->dblink);
		}
		if ($mode == 6) {
			return @mysqli_affected_rows($this->dblink);
		}
		if (@mysqli_num_rows($result) == 0) {
			return 0;
		}
		if ($mode == 1) {
			$res = current(mysqli_fetch_assoc($result));
			if (!$res) {
				return 0;
			}
			return $res;
		}
		if ($mode == 3) {
			return @mysqli_num_rows($result);
		}
		if ($mode == 4) {
			return @mysqli_fetch_assoc($result);
		}
	}

	private function iterateQueryCount() {
		$this->querycount++;
	}

	public function getQueryCount() {
		return $this->querycount;
	}

	public function optimize() {
		global $dbd;
		$result = array_column(mysqli_fetch_all($this->dblink->query('SHOW TABLES')),0);

		foreach ($result as $table_name) {
            $this->query("OPTIMIZE TABLE ".$table_name);
        }
	}

	public function backup() {
		system("/usr/bin/mysqldump -u".DB_USER." -p".DB_PASSWORD." -hlocalhost ".DB_DATABASE." | gzip > ".BACKUP_DIR.date("d-m-Y",time()).".gz");
	}

	/**
	 */
	public function beginTransaction() { #{{{
		$this->query("BEGIN");
	} # }}}

	/**
	 */
	public function commitTransaction() { #{{{
		$this->query("COMMIT");
	} # }}}

	/**
	 */
	public function rollbackTransaction() { #{{{
		$this->query("ROLLBACK");
	} # }}}

	/**
	 */
	public function freeResult($result_id) { #{{{
		mysqli_free_result($result_id);
	} # }}}

}
