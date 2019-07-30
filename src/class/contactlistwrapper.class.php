<?php
class ContactlistWrapper { #{{{

	/**
	 */
	public function __get($userId) { #{{{
		return ContactList::hasContact(currentUser()->getId(),$userId);
	} # }}}

} #}}}


?>
