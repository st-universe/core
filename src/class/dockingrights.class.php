<?php

use Stu\Orm\Repository\FactionRepositoryInterface;

class DockingRightsData extends BaseTable {

	protected $tablename = 'stu_dockingrights';
	const tablename = 'stu_dockingrights';

	function __construct(&$data=array()) {
		$this->data = $data;
	}

	public function getShipId() {
		return $this->data['ships_id'];
	}

	public function setShipId($value) {
		$this->setFieldValue('ships_id',$value,'getShipId');
	}

	public function getTargetId() {
		return $this->data['target'];
	}

	public function setTargetId($value) {
		$this->setFieldValue('target',$value,'getTargetId');
	}

	public function getTargetName() {
		// @todo refactor
		global $container;
		switch ($this->getPrivilegeType()) {
			case DOCK_PRIVILEGE_USER:
				return ResourceCache()->getObject('user',$this->getTargetId())->getName();
			case DOCK_PRIVILEGE_ALLIANCE:
				return ResourceCache()->getObject('alliance',$this->getTargetId())->getName();
			case DOCK_PRIVILEGE_FACTION:
				return $container->get(FactionRepositoryInterface::class)->find((int) $this->getTargetId());

		}
		return ResourceCache()->getObject('ship',$this->getTargetId())->getName();
	}

	public function getPrivilegeType() {
		return $this->data['privilege_type'];
	}

	public function setPrivilegeType($value) {
		$this->setFieldValue('privilege_type',$value,'getPrivilegeType');
	}

	public function getPrivilegeMode() {
		return $this->data['privilege_mode'];
	}

	public function setPrivilegeMode($value) {
		$this->setFieldValue('privilege_mode',$value,'getPrivilegeMode');
	}

	public function getPrivilegeModeString() {
		if ($this->getPrivilegeMode() == DOCK_PRIVILEGE_MODE_ALLOW) {
			return "Erlaubt";
		}
		return "Verboten";
	}

	public function isDockingAllowed() {
		return $this->getPrivilegeMode() == DOCK_PRIVILEGE_MODE_ALLOW;
	}
}
class DockingRights extends DockingRightsData {

	function __construct($crewId=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$crewId." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($crewId);
		}
		return parent::__construct($result);
	}

	static function getConfigByShipId($shipId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE ships_id=".intval($shipId));
		return self::_getList($result,'DockingRightsData');
	}

	static function getBy($shipId,$targetId,$typeId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE ships_id=".intval($shipId)." AND target=".intval($targetId)." AND privilege_type=".intval($typeId));
		return self::_getList($result,'DockingRightsData');
	}

	static function checkPrivilegeFor($shipId, UserData $user) {
		$privileges = self::getConfigByShipId($shipId);
		if (count($privileges) == 0) {
			return FALSE;
		}
		$allowed = FALSE;
		foreach ($privileges as $key => $priv) {
			switch ($priv->getPrivilegeType()) {
				case DOCK_PRIVILEGE_USER:
					if ($priv->getTargetId() == $user->getId()) {
						if ($priv->getPrivilegeMode() == DOCK_PRIVILEGE_MODE_DENY) {
							return FALSE;
						}
						$allowed = TRUE;
					}
					break;
				case DOCK_PRIVILEGE_ALLIANCE:
					if ($priv->getTargetId() == $user->getAllianceId()) {
						if ($priv->getPrivilegeMode() == DOCK_PRIVILEGE_MODE_DENY) {
							return FALSE;
						}
						$allowed = TRUE;
					}
					break;
				case DOCK_PRIVILEGE_FACTION:
					if ($priv->getTargetId() == $user->getFaction()) {
						if ($priv->getPrivilegeMode() == DOCK_PRIVILEGE_MODE_DENY) {
							return FALSE;
						}
						$allowed = TRUE;
					}
					break;
			}
		}
		return $allowed;
	}
}
?>
