<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowTradePostInfo;

use AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use TradePost;

final class ShowTradePostInfo implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_TRADEPOST_INFO';

    private $showTradePostInfoRequest;

    private $tradeLicenseRepository;

    private $talFactory;

    public function __construct(
        ShowTradePostInfoRequestInterface $showTradePostInfoRequest,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeLibFactoryInterface $talFactory
    ) {
        $this->showTradePostInfoRequest = $showTradePostInfoRequest;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->talFactory = $talFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $trade_post = new TradePost($this->showTradePostInfoRequest->getTradePostId());

        if (!$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, (int) $trade_post->getId())) {
            throw new AccessViolation();
        }

        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/trademacros.xhtml/tradepostinfo');
        $game->setPageTitle(_('Handelsposten Details'));

        $game->setTemplateVar(
            'TRADE_POST_INFO',
            $this->talFactory->createTradeAccountTal($trade_post, $userId)
        );
    }
}