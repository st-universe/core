<?php
include_once("config.inc.php");
include_once(APP_PATH."src/class/stringparser_bbcode.class.php");
function BBCode() {
	static $BBCode = NULL;
	if ($BBCode === NULL) {
		$BBCode = new StringParser_BBCode ();
		$BBCode->addCode ('b', 'simple_replace', null, array ('start_tag' => '<b>', 'end_tag' => '</b>'),'inline', array ('block', 'inline', 'color', 'i'), array ());
		$BBCode->addCode ('i', 'simple_replace', null, array ('start_tag' => '<i>', 'end_tag' => '</i>'),'inline', array ('block', 'inline', 'color', 'b'), array ());
		$BBCode->addCode ('color', 'callback_replace', 'bbHandleColor', array ('usecontent_param' => 'default'),'color', array ('b', 'i', 'inline','block'),array());
		$BBCode->setCodeFlag('color','closetag',BBCODE_CLOSETAG_MUSTEXIST);
	}
	return $BBCode;
}
?>
