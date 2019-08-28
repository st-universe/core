<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowTakeOffer;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use TradeOffer;

final class ShowTakeOffer implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_TAKE_OFFER';

    private $showTakeOfferRequest;

    public function __construct(
        ShowTakeOfferRequestInterface $showTakeOfferRequest
    ) {
        $this->showTakeOfferRequest = $showTakeOfferRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $selectedOffer = new TradeOffer($this->showTakeOfferRequest->getOfferId());

        // @todo check if the trade post is allowed

        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/trademacros.xhtml/takeoffer');
        $game->setPageTitle(_('Angebot annehmen'));

        $game->setTemplateVar(
            'SELECTED_OFFER',
            $selectedOffer
        );
    }
}