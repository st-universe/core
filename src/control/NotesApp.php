<?php

final class NotesApp extends gameapp {

	private $default_tpl = "html/notes.xhtml";

	function __construct() {
		parent::__construct($this->default_tpl,"/ Notizen");
		$this->addNavigationPart(new Tuple("notes.php","Notizen"));

		$this->addCallback('B_SAVE_NOTE','saveNote',TRUE);
		$this->addCallback('B_DEL_NOTE','deleteNote',TRUE);
		$this->addCallback('B_DELETE_NOTES','deleteNotes');

		$this->addView('SHOW_NOTE','showNote');

		$this->render($this);
	}

	private $notes = NULL;

	public function getNotesList() {
		if ($this->notes === NULL) {
			$this->notes = Notes::getListByUser(currentUser()->getId());
		}
		return $this->notes;
	}

	protected function showNote() {
		$this->setPageTitle("Notiz: ".$this->getSelectedNote()->getTitleDecoded());
		$this->addNavigationPart(new Tuple("notes.php?SHOW_NOTE=1&note=".$this->getSelectedNote()->getId(),$this->getSelectedNote()->getTitleDecoded()));
		$this->setTemplateFile('html/ajaxempty.xhtml');
		$this->setAjaxMacro('html/notes.xhtml/note');
	}

	private $note = NULL;

	public function getSelectedNote() {
		if ($this->note === NULL) {
			$noteId = Request::indInt('note');
			if (!$noteId || $noteId == 0) {
				$this->note = new NotesData;
				$this->note->setTitle('Neue Notiz');
			} else {
				$this->note = new Notes(Request::indInt('note'));
				$this->getSelectedNote()->forceOwnedByCurrentUser();
			}
		}
		return $this->note;
	}

	protected function saveNote() {
		$title = Request::postString('title');
		$text = Request::postString('text');
		
		$this->getSelectedNote()->setText(strip_tags(tidyString($text)));
		$this->getSelectedNote()->setTitle(strip_tags(tidyString($title)));
		
		if (strlen(trim($text)) == 0) {
			$this->addInformation('Es wurde kein Text eingegeben');
			return;
		}
		$this->getSelectedNote()->setDate(time());
		$this->getSelectedNote()->setUserId(currentUser()->getId());
		$this->getSelectedNote()->save();

		$this->addInformation('Die Notiz wurde gespeichert');
	}

	protected function deleteNote() {
		$this->getSelectedNote()->deleteFromDatabase();
		$this->addInformation("Die Notiz wurde gelöscht");
	}

	/**
	 */
	protected function deleteNotes() {
		$notes = request::postArray('delnotes');
		foreach ($notes as $key => $note) {
			$obj = new Notes(intval($note));
			if (!$obj) {
				continue;
			}
			$obj->forceOwnedByCurrentUser();
			$obj->deleteFromDatabase();
		}
		$this->addInformation(_("Die ausgewählten Notizen wurden gelöscht"));
	}

}
