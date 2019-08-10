<?php

class options extends gameapp {

	private $default_tpl = "html/options.xhtml";

	function __construct() {
		parent::__construct($this->default_tpl,"/ Optionen");
		$this->addNavigationPart(new Tuple("options.php","Optionen"));

		$this->addCallBack("B_CHANGE_NAME","changeUserName");
		$this->addCallBack("B_CHANGE_PASSWORD","changePassword");
		$this->addCallBack("B_CHANGE_EMAIL","changeEmail");
		$this->addCallBack("B_CHANGE_AVATAR","changeAvatar");
		$this->addCallBack("B_CHANGE_DESCRIPTION","changeDescription");
		$this->addCallBack("B_CHANGE_SETTINGS","changeSettings");

		$this->render($this);
	}

	function changeUsername() {
		$value = request::postStringFatal('uname');
		$value = strip_tags(tidyString($value));
		if (strlen($value) < 6) {
			$this->addInformation('Der Siedlername muss aus mindestens 6 Zeichen bestehen');
			return;
		}
		if (strlen($value) > 255) {
			$this->addInformation('Der Siedlername darf inklusive BBCode nur maximal 255 Zeichen lang sein');
			return;
		}
		if (strlen(strip_tags(BBCode()->parse($value))) > 60) {
			$this->addInformation('Der Siedlername darf nur maximal 60 Zeichen lang sein');
			return;
		}
		currentUser()->setUser($value);
		currentUser()->save();
		$this->setSessionVar("username", currentUser()->getName());
		$this->addInformation("Dein Name wurde geändert");
	}

	function changePassword() {
		$oldpass = request::postString('oldpass');
		if (!$oldpass) {
			$this->addInformation("Das alte Passwort wurde nicht angegeben");
			return;
		}
		if (sha1($oldpass) != currentUser()->getPassword()) {
			$this->addInformation('Das alte Passwort ist falsch');
			return;
		}
		$newpass = request::postString('pass');
		$newpass2 = request::postString('pass2');
		if (!$newpass) {
			$this->addInformation("Es wurde kein neues Passwort eingegeben");
			return;
		}
		if (!ereg("^[a-zA-Z0-9]{6,20}$",$newpass)) {
			$this->addInformation('Das Passwort darf nur aus Zahlen und Buchstaben bestehen und muss zwischen 6 und 20 Zeichen lang sein');
			return;
		}
		if ($newpass != $newpass2) {
			$this->addInformation('Die eingegebenen Passwörter stimmen nichberein');
			return;
		}
		currentUser()->setPassword(User::hashPassword($newpass));
		currentUser()->save();
		$this->addInformation("Das Passwort wurde geändert");
	}

	function changeEmail() {
		$value = request::postString('email');
		if (!eregi("^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$",$value)) {
			$this->addInformation('Die E-Mailadresse ist ungültig');
			return;
		}
		currentUser()->setEmail($value);
		currentUser()->save();
		$this->addInformation("Deine E-Mailadresse wurde geändert");
	}

	function changeAvatar() {
		$file = $_FILES['avatar'];
		if ($file['type'] != "image/png") {
			$this->addInformation('Es können nur Bilder im PNG-Format hochgeladen werden');
			return;
		}
		if ($file['size'] > 200000) {
			$this->addInformation('Die maximale Dateigröße liegt bei 200 Kilobyte');
			return;
		}
		if ($file['size'] == 0) {
			$this->addInformation('Die Datei ist leer');
			return;
		}
		if ($this->getSessionVar('propic') != "") {
			@unlink(AVATAR_USER_PATH.$this->getSessionVar('propic').".png");
		}
		$imageName = md5(currentUser()->getId()."_".time());
		$img = imagecreatefrompng($file['tmp_name']);
		$newImage = imagecreatetruecolor(150,150);
		imagecopy($newImage,$img,0,0,0,0,150,150);
		imagepng($newImage,AVATAR_USER_PATH.$imageName.".png");
		currentUser()->setAvatar($imageName);
		currentUser()->save();
		$this->addInformation("Das Bild wurde erfolgreich hochgeladen");
	}

	function changeDescription() {
		$value = request::postStringFatal('description');
		$value = strip_tags(tidyString($value));
		currentUser()->setDescription($value);
		currentUser()->save();
		$this->addInformation("Deine Beschreibung wurde geändert");
	}

	function setEmailNotification(&$reqvar) {
		$value = request::postString($reqvar);
		if ($value && $this->getUser()->getEmailNotification()) {
			return;
		}
		if (!$value && !$this->getUser()->getEmailNotification()) {
			return;
		}
		$this->updateField("email_not",$value ? 1 : 0);
		$this->addInformation("Emailbenachrichtigung geändert");
		$this->getUser()->email_not = $value ? 1 : 0;
	}

	function setStorageNotification(&$reqvar) {
		$value = request::postString($reqvar);
		if ($value && $this->getUser()->getStorageNotification()) {
			return;
		}
		if (!$value && !$this->getUser()->getStorageNotification()) {
			return;
		}
		$this->updateField("lav_not",$value ? 1 : 0);
		$this->addInformation("Lagerbenachrichtigung geändert");
		$this->getUser()->lav_not = $value ? 1 : 0;
	}

	function deleteUser() {
		$this->sendDeletionMail();
		$this->addInformation("Du erhälst in Kürze eine Email um die Accountlöschung zu bestätigen. Die Mail ist bis zum nächsten Tick gültig.");
	}
	function updateField($field,$value) {
		DB()->query("UPDATE stu_user SET ".$field."='".addslashes($value)."' WHERE id=".$this->getUser()->getId());
	}

	function updateProfile($field,$value) {
		DB()->query("UPDATE stu_user_profiles SET ".$field."='".addslashes($value)."' WHERE user_id=".$this->getUser()->getId(),6);
	}

	function getField($field,&$userId) {
		return DB()->query("SELECT ".$field." FROM stu_user WHERE id=".$userId." LIMIT 1",1);
	}

	function checkdelstate($user) { if ($this->db->query("SELECT delmark FROM stu_user WHERE id=".$user,1) != 1) die(show_error(902)); }

	function confirmdel($arr)
	{
		$result = $this->db->query("SELECT id FROM stu_user WHERE id=".$arr['user']." AND pass='".md5($arr['pass'])."' LIMIT 1",1);
		if ($result == 0) exit();
		$this->db->query("UPDATE stu_user SET delmark='2' WHERE id=".$result." LIMIT 1");
	}

	function sendDeletionMail() {
		$this->updateField('delmark',1);
		mail($this->getUser()->email,"Star Trek Universe Accountlöschung","Hallo ".strip_tags($this->getUsername())."\n\n
		Du bekommst diese eMail um die Löschung Deines Accounts zu bestätigen. Fall Du die Löschung nicht ausgelöst hast, ignoriere diese eMail.\n
		Andernfalls klicke auf folgenden Link um die Löschung Deines Accounts zu bestätigen\n\n
		https://stu.wolvnet.de/delaccount.php\n\n
		Beste Grüße\n\n
		Das Star Trek Universe Team","From: Star Trek Universe <automailer@stuniverse.de>
Content-Type: text/plain; charset=UTF-8;");
	}

	protected function changeSettings() {
		$settings = array("email_not" => "setEmailNotification",
				  "save_login" => "setSaveLogin",
				  "storage_not" => "setStorageNotification",
			  	  "show_online" => "setShowOnlineState");
		foreach ($settings as $key => $callback) {
			$value = request::postInt($key);
			if ($value != 1) {
				$value = 0;
			}
			$this->getUser()->$callback($value);
		}
		$this->getUser()->save();
		$this->addInformation("Die Accounteinstellungen wurden aktualisiert");
	}

}
?>
