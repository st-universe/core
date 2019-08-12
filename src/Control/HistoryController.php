<?php

namespace Stu\Control;

use HistoryEntry;
use request;
use Stu\Lib\SessionInterface;
use Tuple;

final class HistoryController extends GameController
{

    private $default_tpl = "html/history.xhtml";

    private $possibleTypes = array(
        1 => "Schiffe",
        2 => "Kolonie",
        3 => "Diplomatie",
        4 => "Sonstiges"
    );

    const MAX_LIMIT = 1000;
    const LIMIT = 50;

    public function __construct(
        SessionInterface $session
    )
    {
        parent::__construct($session, $this->default_tpl, "/ Ereignisse");
        $this->addNavigationPart(new Tuple("history.php", _('Ereignisse')));
    }

    function getHistoryType()
    {
        $type = request::indInt('htype');
        if (!array_key_exists($type, $this->possibleTypes)) {
            return 1;
        }
        return $type;
    }

    function getHistoryCount()
    {
        $count = request::indInt('hcount');
        if (!$count) {
            return self::LIMIT;
        }
        if ($count < 1 || $count > self::MAX_LIMIT) {
            return self::MAX_LIMIT;
        }
        return $count;
    }

    private $history = null;

    function getHistory()
    {
        if ($this->history === null) {
            $this->history = HistoryEntry::getListBy("WHERE type=" . $this->getHistoryType() . " ORDER BY id DESC LIMIT " . $this->getHistoryCount());
        }
        return $this->history;
    }

    function getHistoryTypes()
    {
        $ret = array();
        foreach ($this->possibleTypes as $key => $value) {
            $ret[$key]['name'] = $value;
            $ret[$key]['class'] = $key == $this->getHistoryType() ? 'selected' : '';
            $ret[$key]['count'] = HistoryEntry::countInstances("WHERE type=" . $key);
        }
        return $ret;
    }

}
