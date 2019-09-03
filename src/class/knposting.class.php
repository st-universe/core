<?php

use Stu\Orm\Repository\KnCommentRepositoryInterface;

class KNPostingData extends BaseTable {

	const tablename = 'stu_kn';
	protected $tablename = 'stu_kn';
	
	private $user = NULL;

	function __construct($data=NULL) {
		if ($data === NULL) {
			$data = array("titel" => "","text" => "");
		}
		$this->data = $data;
	}

	function getId() {
		return $this->data['id'];
	}

	function getUserId() {
		return $this->data['user_id'];
	}

	function setUserId($value) {
		$this->data['user_id'] = $value;
		$this->addUpdateField('user_id','getUserId');
	}

	function getTitle() {
		return $this->data['titel'];
	}

	function setTitle($value) {
		$this->data['titel'] = $value;
		$this->addUpdateField('titel','getTitle');
	}

	function getText() {
		return $this->data['text'];
	}

	function setText($value) {
		$this->data['text'] = $value;
		$this->addUpdateField('text','getText');
	}

	function getDate() {
		return $this->data['date'];
	}

	function setDate($value) {
		$this->data['date'] = $value;
		$this->addUpdateField('date','getDate');
	}

	function getEditDate() {
		return $this->data['lastedit'];
	}

	function setEditDate($value) {
		$this->data['lastedit'] = $value;
		$this->addUpdateField('lastedit','getEditDate');
	}

	function getPlotId() {
		return $this->data['plot_id'];
	}

	function setPlotId($value) {
		$this->data['plot_id'] = $value;
		$this->addUpdateField('plot_id','getPlotId');
	}

	private $rpgplot = NULL;

	function getRPGPlot() {
		if ($this->rpgplot === NULL) {
			$this->rpgplot = new RPGPlot($this->getPlotId());
		}
		return $this->rpgplot;
	}

	private $comments = NULL;

	/**
	 */
	public function getComments(): array { #{{{
		if ($this->comments === NULL) {
		    // @todo refactor
			global $container;

			$this->comments = $container->get(KnCommentRepositoryInterface::class)->getByPost((int) $this->getId());
		}
		return $this->comments;
	} # }}}

	/**
	 */
	public function setUserName($value) { # {{{
		$this->setFieldValue('username',$value,'getUserName');
	} # }}}

	/**
	 */
	public function getUserName() { # {{{
		return $this->data['username'];
	} # }}}

}
class KNPosting extends KNPostingData {
	
	function __construct($id=0) {
		$data = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".intval($id)." LIMIT 1",4);
		if ($data == 0) {
			new ObjectNotFoundException($id);
		}
		parent::__construct($data);
	}

	static function countInstances($qry) {
		return DB()->query("SELECT COUNT(id) FROM ".self::tablename." WHERE ".$qry,1);
	}

}
?>
