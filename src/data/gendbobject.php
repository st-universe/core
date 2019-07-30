<?php

/*
 *
 * Copyright 2010 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 *
 */


/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 */
class Attribute {

	/** */
	var $name;

	/** */
	var $type;

	/** */
	var $accessor_name;
	/** */
	var $name_idx;


	/**
	 */
	function __construct($name, $type,$nmeidx) {
		$this->name = trim($name);
		$this->type = trim($type);
		$this->name_idx=$nmeidx;

		$this->accessor_name = str_replace("_", " ", $this->name);
		$this->accessor_name = ucwords($this->accessor_name);
		$this->accessor_name = str_replace(" ", "", $this->accessor_name);
	}

	/**
	 */
	function getSQLType() { #{{{
		return $this->type;
	} # }}}

	/**
	 */
	function getReadCast() {
		switch ($this->type) {
		case "int":
		case "timestamp":
			return "intval";
		case "text":
		default:
			return "strval";
		}
	}

	/**
	 */
	function genDBRead() {
		printf("\t\t\$this-%s(\$data[\"%s\"]);\n",
				$this->accessor_name,
				$this->name
		      );
	}

	/**
	 */
	function genAccessor($ro=false) {
		printf('
	/**
	 * @returns %s
	 */
	public function get%s() {
		return $this->data[\'%s\'];
	}
',
			$this->type,
			$this->accessor_name,
			$this->name
		);

		printf('
	/**
	 * @param value setter for %s(%s)
	 */
	public function set%s(&$value) {
		$this->data[\'%s\'] = %s($value);
	}
',
			$this->name,
			$this->type,
			$this->accessor_name,
			$this->name,
			$this->getReadCast()
		);
	}
}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 */
class POList {

	/** */
	var $name;

	/** */
	var $params;

	/** */
	var $where;

	/** */
	var $orderby;

	/**
	 */
	function __construct($name,$params,$where,$orderby) {
		$this->name = $name;
		$this->params = $params;
		$this->where = $where;
		$this->orderby = $orderby;
	}

	/**
	 */
	function hasOrderBy() {
		return strlen($this->orderby);
	}

	/**
	 */
	function hasWhere() {
		return strlen($this->where);
	}
}
 
/**
 * Creates an object representation of of a config file. the config file looks
 * like this:
 *
 * <pre>
 * table=kunde
 * class=Kunde
 * key=id
 * attribute=name:string
 * attribute=ts:timestamp
 * list=name:params:where:orderby
 * </pre>
 *
 * currently there is only one id field supported and its used to create new
 * ids and reference db entries.
 *
 * @author Daniel Jakob <wolverine@stuniverse.de>
 */
class PersistentObject {

	var	$class;
	var	$classname;
	var	$m_nameOfDeleteMethod="deleteFromDatabase"; // Allow to change this;

	var	$m_useUserAUTH=false;

	/** */
	var $attributes;

	/** */
	var $polists;

	/** */
	var $keyname;

	/** */
	var $verbatim;

	/** */
	var $cacheable;

	/** */
	var $includes;

	/**
	 */
	function __construct($configfile) {
		$this->attributes = array();
		$this->polists = array();
		$this->includes = array();
		$this->verbatim = "";
		$this->cacheable = false;
		$this->_parseConfigFile($configfile);
	}

	/**
	 */
	function _parseConfigFile($configfile) {
		if (!($fh=fopen($configfile,'r'))) {
			trigger_error('can not open file: '.$configfile, E_USER_ERROR);
		}
		$filecontent = fread($fh,filesize($configfile));
		$lines = explode("\n", $filecontent);
		$startverbatim = 0;
		$max=count($lines);
		for ($i=0; $i<$max and !$startverbatim; $i++) {
			if (!strlen($lines[$i])) {
				continue;
			}
			if (preg_match('/^#/', $lines[$i])) {
				continue;
			}
			if (strpos($lines[$i], '=')>0) {
				list($key,$value)=explode('=', $lines[$i], 2);
			} else {
				$key = $lines[$i];
			}
			switch ($key) {
			case "table":
				$this->table = $value;
				break;
			case "class":
				$this->class = $value;
				$this->classname = $value;
				break;
			case "key":
				$this->keyname = $value;
				break;
			case "list":
				list($name,$params,$where,$orderby) = explode(':', $value);
				$polist = new POList($name,$params,$where,$orderby);
				$this->polists[] = $polist;
				break;
			case "attribute":
				$x = explode(':', $value);
				if (!isset($x[2])) {
					$x[2] = "strval";
				}
				if (!isset($x[3])) {
					$x[3] = "strval";
				}
				list($name, $type) = $x;
				$attribute = new Attribute($name, $type,count($this->attributes)+1);
				$this->attributes[] = $attribute;
				break;
			case "withuserauth":
				$this->m_useUserAUTH=true;
				break;
			case "verbatim":
				$startverbatim = $i+1;
				break;
			case "cacheable":
				$this->cacheable = true;
				break;
			case "include":
				$this->includes[] = $value;
				break;
			default:
				trigger_error('unknown key: '.$key);
			}
		}
		for ($i=$startverbatim; $i<$max; $i++) {
			$this->verbatim .= $lines[$i]."\n";
		}
		fclose($fh);
	}

	/**
	 */
	function writePHP($is_writeTableClass=false) {
		
		if ($is_writeTableClass)
		{
			$oldname=$this->class;
			$oldverbatim=$this->verbatim;

			$this->verbatim=""; // EMPTY!!!
			$this->subclass = $this->class;
			$this->class.="_Table"; // add Table
			$this->writePHPTable();
			$this->class=$oldname;
			$this->verbatim=$oldverbatim;
		}
		else
		{
			$this->writePHPClassOnly();
		}
	}
	function writePHPClassOnly() {
		echo "<"."?php\n\n";
	?>
/*
 * Copyright 2011 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 */

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 */
<?php
		echo "class {$this->class} extends {$this->class}_Table { # {{{\n\n";
		echo "\t/**\n";
		echo "\t */\n";
		echo "\tpublic function _create() {\n";
		echo "\t\treturn new {$this->class};\n";
		echo "\t}\n";
		foreach ($this->polists as $polist) {
		?>
		/**
		 */
		static function &getList<?php echo $polist->name?>(<?php echo $polist->params?>) {
			return parent::getList<?php echo $polist->name?>(<?php echo $polist->params;
				if (strlen($polist->params)) echo ",";?>new <?php echo $this->class;?>());
		}
		<?php
		}

		echo $this->verbatim;
		echo "} # }}}\n";
		echo "\n?".">";
	}
	/**
	 */
	function writePHPTable() {
		print("<?php\n");
?>
/* 
 *      === AUTO GENERATED ===
 */

/*
 *
 * Copyright 2011 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 *
 */


<?php
	echo "class {$this->class} extends Base_Table {\n\n";
	echo "\t/**\n";
	echo "\t */\n";
	echo "\tpublic function _create() {\n";
	echo "\t\treturn new {$this->class};\n";
	echo "\t}\n\n";
	echo "\t/**\n";
	echo "\t */\n";
	echo "\tpublic function getTableName() {\n";
	echo "\t\treturn '{$this->table}';\n";
	echo "\t}\n";
	
	echo "\n\tconst SELECTSQL = 'SELECT * FROM {$this->table}';\n";
?>

        /**
         */
        function __construct($id=NULL) {
                if ($id!==NULL) {
                        $this->_loadFromDbByd($id);
                }
        }

        /**
         */
        private function _loadFromDbById($id) {
		$result = DB()->query(self::SELECTSQL.' WHERE id='.$id,4);
		if ($result) {
			$this->_setData($result);
		}
        }

	/** */
	private $data = array();
<?php

        $fa = new Attribute($this->keyname,"int",0);
        $fa->genAccessor(true);
        foreach ($this->attributes as $attribute) {
                $attribute->genAccessor();
        }
        echo $this->verbatim;
?>

        /**
         */
        public function _setData(&$data) {
		$this->data = $data;
        }

        /**
         */
        public function _getData() {
		return $this->data;
        }

<?php
	echo "\t/**";
	echo "\n\t */";
        echo "\n\t".'static public function getObjectsBy($where=FALSE,$order=FALSE,$creator=FALSE,$extra=FALSE) {';
        echo "\n\t\t".'if ($creator === FALSE) {';
        echo "\n\t\t\t".'$creator = new '.$this->classname.';';
        echo "\n\t\t".'}';
        echo "\n\t\t".'$qry = self::SELECTSQL;';
        echo "\n\t\t".'if ($where !== FALSE) {'."\n";
        echo "\t\t\t".'$qry.= " WHERE $where";'."\n";
        echo "\t\t".'}'."\n";
        echo "\t\t".'if ($order !== FALSE) {'."\n";
        echo "\t\t\t".'$qry.= " ORDER BY $order";'."\n";
        echo "\t\t".'}'."\n";
        echo "\t\t".'if ($extra !== FALSE) {'."\n";
        echo "\t\t\t".'$qry.= " $extra ";'."\n";
        echo "\t\t".'}'."\n";
        echo "\t\t".'return parent::_getList($qry, $creator);'."\n";
        echo "\t}";
?>

<?php
	echo "\n\t/**";
	echo "\n\t */";
        echo "\n\t".'static public function countInstances($where=FALSE) {';
        echo "\n\t\t".'return DB()->query("SELECT COUNT(id) FROM ".self::getTableName()." ".($where !== FALSE ? \'WHERE \'.$where : ""),1);';
        echo "\n\t}";
        echo "\n";
	
	echo "\n\t/**";
	echo "\n\t */";
        echo "\n\t".'static public function truncate($where=FALSE) {';
        echo "\n\t\t".'return DB()->query("DELETE FROM ".self::getTableName()." ".($where !== FALSE ? \'WHERE \'.$where : ""));';
        echo "\n\t}";
        echo "\n";
?>

}

<?php
		print "?>";
	}

	/**
	 */
	function writeSQL() {
		printf("CREATE TABLE %s (
			%s serial PRIMARY KEY,
			%s
		);\n", 
		$this->table, $this->keyname, $this->writeSQLAttributes());
		printf("
				GRANT ALL ON %s TO evalanche_dba;
				GRANT ALL ON %s_%s_seq TO evalanche_dba;
				GRANT SELECT, UPDATE, INSERT, DELETE ON %s TO evalanche;
				GRANT SELECT, UPDATE ON %s_%s_seq TO evalanche;
		", 
		$this->table, 
		$this->table, $this->keyname,
		$this->table, 
		$this->table, $this->keyname
		);
	}

	/**
	 */
	function writeSQLAttributes() {
		$l = array();
		foreach ($this->attributes as $attribute) {
			$l[] = sprintf('%s %s', $attribute->name, $attribute->getSQLType());
		}
		return join(",\n", $l);
	}
}

$argc = count($_SERVER["argv"]);
$argt = $_SERVER["argv"][1];
if ($argc!=3) {
	usage();
	exit(1);
}
$po = new PersistentObject($_SERVER["argv"][2]);
if ($argt==="sql") {
	$po->writeSQL();
} else if ($argt=="php") {
	$po->writePHP(false);
} else if ($argt=="table") {
	$po->writePHP(true);
} else {
	usage();
	trigger_error('need two param as first command line param as the config file', FATAL);
}

function usage() {
	print "genpersistentobject {sql|php|table} configfile";
}

?>
