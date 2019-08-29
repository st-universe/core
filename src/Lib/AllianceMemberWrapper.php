<?php

namespace Lib;

use AllianceData;
use UserData;

class AllianceMemberWrapper {

	private $user = NULL;
	private $alliance = NULL;

	function __construct(UserData $user, AllianceData $alliance) {
	        $this->user = $user;
		$this->alliance = $alliance;
	}

	function getUser() {
	        return $this->user;
	}

	function getAlliance() {
	        return $this->alliance;
	}

	function isFounder() {
	        return $this->getUser()->getId() == $this->getAlliance()->getFounder()->getUserId();
	}

	function getUserId() {
	        return $this->getUser()->getId();
	}
}