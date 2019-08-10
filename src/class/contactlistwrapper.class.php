<?php
class ContactlistWrapper { #{{{

	/**
	 */
	public function __get($userId) { #{{{
		return Contactlist::hasContact(currentUser()->getId(),$userId);
	} # }}}

} #}}}


?>
