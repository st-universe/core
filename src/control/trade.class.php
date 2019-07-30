<?php

class Tradeapp extends gameapp {

	private $default_tpl = "html/trade.xhtml";

	function __construct() {
		parent::__construct($this->default_tpl,"/ Handel");
		$this->addNavigationPart(new Tuple("trade.php","Handel"));

		$this->addCallBack("B_CANCEL_OFFER","cancelOffer",TRUE);
		$this->addCallBack("B_CANCEL_OFFER_ACCOUNT","cancelOfferAccount",TRUE);
		$this->addCallBack('B_CREATE_OFFER','createOffer');
		$this->addCallBack('B_TAKE_OFFER','takeOffer',TRUE);
		$this->addCallBack('B_TRANSFER','transferGoods');
		$this->addCallBack('B_ADD_SHOUTBOX_ENTRY','addShoutboxEntry');

		$this->addView('SHOW_TRADEPOST_INFO','showTradePostInfo');
		$this->addView('SHOW_ACCOUNTS','showUserAccounts');
		$this->addView('SHOW_OFFER_GOOD','showOfferByGood');
		$this->addView('SHOW_OFFER_MENU','showOfferMenu');
		$this->addView('SHOW_OFFER_MENU_TRANSFER','showOfferMenuTransfer');
		$this->addView('SHOW_OFFER_MENU_NEW_OFFER','showOfferMenuNewOffer');
		$this->addView('SHOW_TAKE_OFFER','showTakeOffer');
		$this->addView('SHOW_LICENCE_LIST','showTradeLicenceList');
		$this->addView('SHOW_SHOUTBOX','showShoutbox');
		$this->addView('SHOW_SHOUTBOX_LIST','showShoutboxList');

		$this->render($this);
	}

	private $offerList = NULL;

	public function getOfferList() {
		if ($this->offerList === NULL) {
			$this->offerList = TradeOffer::getByLicencedTradePosts(currentUser()->getId());
		}
		return $this->offerList;
	}
	
	protected function showOfferMenu() {
		$this->setTemplateFile('html/ajaxwindow.xhtml');
		$this->setAjaxMacro('html/trademacros.xhtml/tradeoffermenu');
		$this->setPageTitle('Management '.$this->getSelectedStorage()->getGood()->getName());
		$this->getTemplate()->setVar('STOR',$this->getSelectedStorage());
		$this->tradepost = new TradePost($this->getSelectedStorage()->getTradePostId());
	}

	protected function showOfferMenuTransfer() {
		$this->setTemplateFile('html/ajaxempty.xhtml');
		$this->setAjaxMacro('html/trademacros.xhtml/newoffermenu_transfer');
		$this->setPageTitle('Management '.$this->getSelectedStorage()->getGood()->getName());
		$this->getTemplate()->setVar('STOR',$this->getSelectedStorage());
		$this->tradepost = new TradePost($this->getSelectedStorage()->getTradePostId());
	}

	protected function showOfferMenuNewOffer() {
		$this->setTemplateFile('html/ajaxempty.xhtml');
		$this->setAjaxMacro('html/trademacros.xhtml/newoffermenu_newoffer');
		$this->setPageTitle('Management '.$this->getSelectedStorage()->getGood()->getName());
		$this->getTemplate()->setVar('STOR',$this->getSelectedStorage());
		$this->tradepost = new TradePost($this->getSelectedStorage()->getTradePostId());
	}

	protected function showTradeLicenceList() {
		$this->setTemplateFile('html/ajaxwindow.xhtml');
		$this->setAjaxMacro('html/trademacros.xhtml/tradelicencelist');
		$this->setPageTitle('Liste ausgestellter Lizenzen');
		$this->tradepost = new TradePost(request::getIntFatal('postid'));
		if (!$this->getTradePost()->currentUserHasLicence()) {
			new AccessViolation;
		}
		$this->getTemplate()->setVar('LIST',TradeLicences::getLicencesByTradePost($this->getTradePost()->getId()));
	}

	private $selectedStorage = NULL;

	public function getSelectedStorage() {
		if ($this->selectedStorage === NULL) {
			$this->selectedStorage = new TradeStorage(Request::indInt('storid'));
			if ($this->selectedStorage->getUserId() != currentUser()->getId()) {
				new AccessViolation;
			}
		}
		return $this->selectedStorage;
	}

	protected function showTradePostInfo() {
		$this->setTemplateFile('html/ajaxwindow.xhtml');
		$this->setAjaxMacro('html/trademacros.xhtml/tradepostinfo');
		$this->setPageTitle('Handelsposten Details');
		$this->tradepost = new TradePost(Request::getIntFatal('postid'));
	}

	protected function showTakeOffer() {
		$this->setTemplateFile('html/ajaxwindow.xhtml');
		$this->setAjaxMacro('html/trademacros.xhtml/takeoffer');
		$this->setPageTitle('Angebot annehmen');
		$this->tradepost = $this->getSelectedOffer()->getTradePostId();
	}

	private $selectedOffer = NULL;

	public function getSelectedOffer() {
		if ($this->selectedOffer === NULL) {
			$this->selectedOffer = new TradeOffer(Request::indInt('offerid'));
		}
		return $this->selectedOffer;
	}
	
	protected function showOfferByGood() {
		$this->setTemplateFile('html/ajaxwindow.xhtml');
		$this->setAjaxMacro('html/trademacros.xhtml/offerbygood');
		$this->setPageTitle('Angebote mit '.ResourceCache()->getObject('good',Request::getIntFatal('goodid'))->getName());
		$this->tradepost = new TradePost(Request::getIntFatal('postid'));
		$this->getTemplate()->setVar('OFFER',TradeOffer::getOfferByGood($this->getTradePost()->getId(),currentUser()->getId(),Request::getIntFatal('goodid')));
	}
	
	protected function showUserAccounts() {
		$this->addNavigationPart(new Tuple("trade.php?SHOW_ACCOUNTS=1","Warenkonten"));
		$this->setTemplateFile('html/tradeaccounts.xhtml');
	}

	private $tradepost = NULL;

	private $accounts = NULL;

	public function getAccounts() {
		if ($this->accounts === NULL) {
			$this->accounts = TradePost::getListByLicences(currentUser()->getId());
		}
		return $this->accounts;
	}

	public function getTransferableTradePosts() {
		$ret = array();
		foreach ($this->getAccounts() as $key => $obj) {
			if ($this->tradepost->getId() != $obj->getId() && $obj->getTradeNetwork() == $this->tradepost->getTradeNetwork()) {
				$ret[] = $obj;
			}
		}
		return $ret;
	}

	protected function cancelOfferAccount() {
		$this->setView('SHOW_ACCOUNTS');
		$this->cancelOffer();
	}

	protected function cancelOffer() {
		$offerId = Request::getIntFatal('offerid');
		$offer = new TradeOffer($offerId);
		if ($offer->getUserId() != currentUser()->getId()) {
			new AccessViolation;
		}
		$offer->getTradePost()->upperStorage(currentUser()->getId(),$offer->getOfferedGoodId(),$offer->getOfferedGoodCount()*$offer->getOfferCount());
		$offer->deleteFromDatabase();
		$this->addInformation('Das Angebot wurde gelöscht');
	}

	public function selectedIsDilithium() {
		return $this->getSelectedStorage()->getGoodId() == GOOD_DILITHIUM;
	}

	public function getSelectableGoods() {
		return Good::getGoodsBy('WHERE view=1 AND tradeable=1 AND illegal_'.$this->getTradePost()->getTradeNetwork().'=0 ORDER BY sort');
	}

	protected function createOffer() {
		$ggoodId = Request::postIntFatal('ggid');
		$wgoodId = Request::postIntFatal('wgid');
		$wcount = Request::postIntFatal('wcount');
		$gcount = Request::postIntFatal('gcount');
		$amount = Request::postIntFatal('amount');

		if ($ggoodId == $wgoodId) {
			return;
		}
		if ($gcount < 1 || $wcount < 1) {
			return;
		}
		$this->tradepost = $this->getSelectedStorage()->getTradePost();
		if ($this->getTradePost()->getStorageSum() > $this->getTradePost()->getStorage()) {
			$this->setView("SHOW_ACCOUNTS");
			$this->addInformation("Dein Warenkonto auf diesem Handelsposten ist überfüllt - Angebot kann nicht erstellt werden");
			return;
		}
		if ($ggoodId == GOOD_DILITHIUM) {
			if (!array_key_exists($wgoodId,$this->getSelectableGoods())) {
				return;
			}
		} else {
			if ($wgoodId != GOOD_DILITHIUM) {
				return;
			}
		}
		if ($amount < 1 || $amount > 99) {
			$amount = 1;
		}
		if ($amount * $gcount > $this->getSelectedStorage()->getCount()) {
			$amount = floor($this->getselectedStorage()->getCount()/$gcount);
		}
		if ($amount < 1) {
			return;
		}
		$offer = new TradeOfferData;
		$offer->setUserId(currentUser()->getId());
		$offer->setTradePostId($this->getSelectedStorage()->getTradePostId());
		$offer->setDate(time());
		$offer->setOfferedGoodId($ggoodId);
		$offer->setOfferedGoodCount($gcount);
		$offer->setWantedGoodId($wgoodId);
		$offer->setWantedGoodCount($wcount);
		$offer->setOfferCount($amount);
		$offer->save();

		if ($this->getSelectedStorage()->getCount() <= $amount*$gcount) {
			$this->getSelectedStorage()->deleteFromDatabase();
		} else {
			$this->getSelectedStorage()->lowerCount($amount*$gcount);
			$this->getSelectedStorage()->save();
		}

		$this->addInformation('Das Angebot wurde erstellt');
	}

	protected function takeOffer() {
		$amount = Request::postIntFatal('amount');
		if (currentUser()->getId() == $this->getSelectedOffer()->getUserId()) {
			return;
		}
		$storage = TradeStorage::getStorageByGood($this->getSelectedOffer()->getTradePostId(),currentUser()->getId(),$this->getSelectedOffer()->getWantedGoodId());
		if (!$storage || $storage->getCount() < $this->getSelectedOffer()->getWantedGoodCount()) {
			$this->addInformation('Es befindet sich nicht genügend '.$this->getSelectedOffer()->getWantedGoodObject()->getName().' auf diesem Handelsposten');
			return;
		}
		$wrap = new TradePostStorageWrapper($storage->getTradePostId(),currentUser()->getId());
		if ($wrap->getStorageSum() > $storage->getTradePost()->getStorage() && $this->getSelectedOffer()->getOfferedGoodCount() > $this->getSelectedOffer()->getWantedGoodCount()) {
			$this->addInformation('Dein Warenkonto auf diesem Handelsposten ist voll');
			return;
		}
		if ($amount*$this->getSelectedOffer()->getWantedGoodCount() > $storage->getCount()) {
			$amount = floor($storage->getCount()/$this->getSelectedOffer()->getWantedGoodCount());
		}
		if ($amount*$this->getSelectedOffer()->getOfferedGoodCount()-$amount*$this->getSelectedOffer()->getWantedGoodCount() > $storage->getTradePost()->getStorage()-$wrap->getStorageSum()) {
			$amount = floor(($storage->getTradePost()->getStorage()-$wrap->getStorageSum())/($this->getSelectedOffer()->getOfferedGoodCount()-$this->getSelectedOffer()->getWantedGoodCount()));
			if ($amount <= 0) {
				$this->addInformation('Es steht für diese Transaktion nicht genügend Platz in deinem Warenkonto zur Verfügung');
				return;
			}
		}
		DB()->beginTransaction();
		if ($this->getSelectedOffer()->getOfferCount() <= $amount) {
			$amount = $this->getSelectedOffer()->getOfferCount();
			$this->getSelectedOffer()->deleteFromDatabase();
		} else {
			$this->getSelectedOffer()->lowerOfferCount($amount);
			$this->getSelectedOffer()->save();
		}
		$storage->getTradePost()->upperStorage($this->getSelectedOffer()->getUserId(),$this->getSelectedOffer()->getWantedGoodId(),$this->getSelectedOffer()->getWantedGoodCount()*$amount);
		$storage->getTradePost()->upperStorage(currentUser()->getId(),$this->getSelectedOffer()->getOfferedGoodId(),$this->getSelectedOffer()->getOfferedGoodCount()*$amount);
		$storage->getTradePost()->lowerStorage(currentUser()->getId(),$this->getSelectedOffer()->getWantedGoodId(),$this->getSelectedOffer()->getWantedGoodCount()*$amount);

		DB()->commitTransaction();
		$this->addInformation("Das Angebot wurde ".$amount." mal angenommen");
		PM::sendPM(currentUser()->getId(),$this->getSelectedOffer()->getUserId(),'Es wurden insgesamt '.($this->getSelectedOffer()->getOfferedGoodCount()*$amount).' '.$this->getSelectedOffer()->getOfferedGoodObject()->getName().' gegen '.($this->getSelectedOffer()->getWantedGoodCount()*$amount).' '.$this->getSelectedOffer()->getWantedGoodObject()->getName().' getauscht',PM_SPECIAL_TRADE);
	}

	public function getTradePost() {
		return $this->tradepost;
	}

	protected function transferGoods() {
		$this->setView('SHOW_ACCOUNTS');
		$count = Request::postIntFatal('count');
		$target = Request::postIntFatal('target');

		$this->tradepost = $this->getSelectedStorage()->getTradePost();
		if ($this->getSelectedStorage()->getCount() < $count) {
			$count = $this->getSelectedStorage()->getCount();
		}
		if ($count < 1) {
			return;
		}

		if ($this->getTradePost()->getFreeTransferCapacity() <= 0) {
			$this->addInformation("Du hast an diesem Posten derzeit keine freie Transferkapaziztät");
			return;
		}
		$targetpost = new TradePost($target);
		if (!$targetpost->currentUserHasLicence()) {
			return;
		}
		if ($targetpost->getTradeNetwork() != $this->getTradePost()->getTradeNetwork()) {
			return;
		}
		if ($targetpost->getStorageSum() >= $targetpost->getStorage()) {
			return;
		}
		if ($count + $targetpost->getStorageSum() > $targetpost->getStorage()) {
			$count = $targetpost->getStorage()-$targetpost->getStorageSum();
		}
		if ($count > $this->getTradePost()->getFreeTransferCapacity()) {
			$count = $this->getTradePost()->getFreeTransferCapacity();
		}
		$targetpost->upperStorage(currentUser()->getId(),$this->getSelectedStorage()->getGoodId(),$count);
		$this->getTradePost()->lowerStorage(currentUser()->getId(),$this->getSelectedStorage()->getGoodId(),$count);
		TradeTransfer::registerTransfer($this->getTradePost()->getId(),currentUser()->getId(),$count);
		$this->addInformation("Es wurde ".$count." ".$this->getSelectedStorage()->getGood()->getName()." zum ".$targetpost->getName()." transferiert");
	}

	public function getTradeLicenceCount() {
		return TradeLicences::countInstances('user_id='.currentUser()->getId());
	}
	
	protected function showShoutbox() {
		if (!TradeLicences::hasLicenceInNetwork(currentUser()->getId(),request::getIntFatal('network'))) {
			throw new AccessViolation;
		}
		$this->setTemplateFile('html/ajaxwindow.xhtml');
		$this->setAjaxMacro('html/trademacros.xhtml/shoutbox');
		$this->setPageTitle(_('Schwarzes Brett'));
		$this->getTemplate()->setVar('NETWORK',request::getIntFatal('network'));
		$this->getTemplate()->setRef('SHOUTBOX',TradeShoutbox::getByTradeNetworkId(request::getIntFatal('network')));
	}

	/**
	 */
	protected function showShoutboxList() { #{{{
		if (!TradeLicences::hasLicenceInNetwork(currentUser()->getId(),request::getIntFatal('network'))) {
			throw new AccessViolation;
		}
		$this->setTemplateFile('html/ajaxempty.xhtml');
		$this->setAjaxMacro('html/trademacros.xhtml/shoutbox_entries');
		$this->getTemplate()->setRef('SHOUTBOX',TradeShoutbox::getByTradeNetworkId(request::getIntFatal('network')));
	} # }}}

	/**
	 */
	protected function addShoutboxEntry() { #{{{
		$msg = request::postString('shoutboxentry');
		$tradeNetworkId = request::postIntFatal('network');
		if (!TradeLicences::hasLicenceInNetwork(currentUser()->getId(),$tradeNetworkId)) {
			throw new AccessViolation;
		}
		$msg = encodeString(substr(strip_tags($msg),0,200));
		if (strlen($msg) > 0) {
			$entry = new TradeShoutBoxData;
			$entry->setUserId(currentUser()->getId());
			$entry->setDate(time());
			$entry->setTradeNetworkId($tradeNetworkId);
			$entry->setMessage($msg);
			$entry->save();
		}
		TradeShoutbox::deleteHistory($tradeNetworkId);
		$this->setTemplateFile('html/ajaxempty.xhtml');
		$this->setAjaxMacro('html/trademacros.xhtml/shoutbox_entries');
		$this->getTemplate()->setRef('SHOUTBOX',TradeShoutbox::getByTradeNetworkId(request::postIntFatal('network')));
	} # }}}

}
?>
