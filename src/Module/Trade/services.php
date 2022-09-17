<?php

declare(strict_types=1);

namespace Stu\Module\Trade;

use Stu\Module\Control\GameController;
use Stu\Module\Trade\Action\AddShoutBoxEntry\AddShoutBoxEntry;
use Stu\Module\Trade\Action\AddShoutBoxEntry\AddShoutBoxEntryRequest;
use Stu\Module\Trade\Action\AddShoutBoxEntry\AddShoutBoxEntryRequestInterface;
use Stu\Module\Trade\Action\BasicTradeBuy\BasicTradeBuy;
use Stu\Module\Trade\Action\BasicTradeSell\BasicTradeSell;
use Stu\Module\Trade\Action\CancelOffer\CancelOffer;
use Stu\Module\Trade\Action\CancelOffer\CancelOfferRequest;
use Stu\Module\Trade\Action\CancelOffer\CancelOfferRequestInterface;
use Stu\Module\Trade\Action\ChangeTradePostName\ChangeTradePostName;
use Stu\Module\Trade\Action\ChangeTradePostName\ChangeTradePostNameRequest;
use Stu\Module\Trade\Action\ChangeTradePostName\ChangeTradePostNameRequestInterface;
use Stu\Module\Trade\Action\CreateLicense\CreateLicense;
use Stu\Module\Trade\Action\CreateLicense\CreateLicenseRequest;
use Stu\Module\Trade\Action\CreateLicense\CreateLicenseRequestInterface;
use Stu\Module\Trade\Action\CreateOffer\CreateOffer;
use Stu\Module\Trade\Action\CreateOffer\CreateOfferRequest;
use Stu\Module\Trade\Action\CreateOffer\CreateOfferRequestInterface;
use Stu\Module\Trade\Action\TakeOffer\TakeOffer;
use Stu\Module\Trade\Action\TakeOffer\TakeOfferRequest;
use Stu\Module\Trade\Action\TakeOffer\TakeOfferRequestInterface;
use Stu\Module\Trade\Action\TransferGoods\TransferGoods;
use Stu\Module\Trade\Action\TransferGoods\TransferGoodsRequest;
use Stu\Module\Trade\Action\TransferGoods\TransferGoodsRequestInterface;
use Stu\Module\Trade\Lib\TradeLibFactory;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\Overview\Overview;
use Stu\Module\Trade\View\ShowAccounts\ShowAccounts;
use Stu\Module\Trade\View\ShowBasicTrade\ShowBasicTrade;
use Stu\Module\Trade\View\ShowLicenseList\ShowLicenseList;
use Stu\Module\Trade\View\ShowLicenseList\ShowLicenseListRequest;
use Stu\Module\Trade\View\ShowLicenseList\ShowLicenseListRequestInterface;
use Stu\Module\Trade\View\ShowLicenseMenu\ShowLicenseMenu;
use Stu\Module\Trade\View\ShowLicenseMenu\ShowLicenseMenuRequest;
use Stu\Module\Trade\View\ShowLicenseMenu\ShowLicenseMenuRequestInterface;
use Stu\Module\Trade\View\ShowLicenseInfo\ShowLicenseInfo;
use Stu\Module\Trade\View\ShowLicenseInfo\ShowLicenseInfoRequest;
use Stu\Module\Trade\View\ShowLicenseInfo\ShowLicenseInfoRequestInterface;
use Stu\Module\Trade\View\ShowOfferGood\ShowOfferGood;
use Stu\Module\Trade\View\ShowOfferGood\ShowOfferGoodRequest;
use Stu\Module\Trade\View\ShowOfferGood\ShowOfferGoodRequestInterface;
use Stu\Module\Trade\View\ShowOfferMenu\ShowOfferMenu;
use Stu\Module\Trade\View\ShowOfferMenu\ShowOfferMenuRequest;
use Stu\Module\Trade\View\ShowOfferMenu\ShowOfferMenuRequestInterface;
use Stu\Module\Trade\View\ShowOfferMenuNewOffer\ShowOfferMenuNewOffer;
use Stu\Module\Trade\View\ShowOfferMenuNewOffer\ShowOfferMenuNewOfferRequest;
use Stu\Module\Trade\View\ShowOfferMenuNewOffer\ShowOfferMenuNewOfferRequestInterface;
use Stu\Module\Trade\View\ShowSearch\ShowSearchBoth;
use Stu\Module\Trade\View\ShowSearch\ShowSearchDemand;
use Stu\Module\Trade\View\ShowSearch\ShowSearchOffer;
use Stu\Module\Trade\View\ShowShiplist\ShowShiplist;
use Stu\Module\Trade\View\ShowShoutBox\ShowShoutBox;
use Stu\Module\Trade\View\ShowShoutBox\ShowShoutBoxRequest;
use Stu\Module\Trade\View\ShowShoutBox\ShowShoutBoxRequestInterface;
use Stu\Module\Trade\View\ShowShoutBoxList\ShowShoutBoxList;
use Stu\Module\Trade\View\ShowShoutBoxList\ShowShoutBoxListRequest;
use Stu\Module\Trade\View\ShowShoutBoxList\ShowShoutBoxListRequestInterface;
use Stu\Module\Trade\View\ShowTakeOffer\ShowTakeOffer;
use Stu\Module\Trade\View\ShowTakeOffer\ShowTakeOfferRequest;
use Stu\Module\Trade\View\ShowTakeOffer\ShowTakeOfferRequestInterface;
use Stu\Module\Trade\View\ShowTradePostInfo\ShowTradePostInfo;
use Stu\Module\Trade\View\ShowTradePostInfo\ShowTradePostInfoRequest;
use Stu\Module\Trade\View\ShowTradePostInfo\ShowTradePostInfoRequestInterface;
use Stu\Module\Trade\View\ShowTransferMenu\ShowTransferMenu;
use Stu\Module\Trade\View\ShowTransferMenu\ShowTransferMenueRequest;
use Stu\Module\Trade\View\ShowTransferMenu\ShowTransferMenueRequestInterface;
use function DI\autowire;

return [
    TradeLibFactoryInterface::class => autowire(TradeLibFactory::class),
    ShowLicenseMenuRequestInterface::class => autowire(ShowLicenseMenuRequest::class),
    ShowLicenseInfoRequestInterface::class => autowire(ShowLicenseInfoRequest::class),
    ShowOfferMenuRequestInterface::class => autowire(ShowOfferMenuRequest::class),
    ShowTransferMenueRequestInterface::class => autowire(ShowTransferMenueRequest::class),
    ShowOfferMenuNewOfferRequestInterface::class => autowire(ShowOfferMenuNewOfferRequest::class),
    CreateOfferRequestInterface::class => autowire(CreateOfferRequest::class),
    ChangeTradePostNameRequestInterface::class => autowire(ChangeTradePostNameRequest::class),
    ShowTakeOfferRequestInterface::class => autowire(ShowTakeOfferRequest::class),
    ShowTradePostInfoRequestInterface::class => autowire(ShowTradePostInfoRequest::class),
    TakeOfferRequestInterface::class => autowire(TakeOfferRequest::class),
    CancelOfferRequestInterface::class => autowire(CancelOfferRequest::class),
    CreateLicenseRequestInterface::class => autowire(CreateLicenseRequest::class),
    ShowLicenseListRequestInterface::class => autowire(ShowLicenseListRequest::class),
    ShowOfferGoodRequestInterface::class => autowire(ShowOfferGoodRequest::class),
    ShowShoutBoxRequestInterface::class => autowire(ShowShoutBoxRequest::class),
    ShowShoutBoxListRequestInterface::class => autowire(ShowShoutBoxListRequest::class),
    AddShoutBoxEntryRequestInterface::class => autowire(AddShoutBoxEntryRequest::class),
    TransferGoodsRequestInterface::class => autowire(TransferGoodsRequest::class),
    'TRADE_ACTIONS' => [
        AddShoutBoxEntry::ACTION_IDENTIFIER => autowire(AddShoutBoxEntry::class),
        BasicTradeBuy::ACTION_IDENTIFIER => autowire(BasicTradeBuy::class),
        BasicTradeSell::ACTION_IDENTIFIER => autowire(BasicTradeSell::class),
        CancelOffer::ACTION_IDENTIFIER => autowire(CancelOffer::class),
        ChangeTradePostName::ACTION_IDENTIFIER => autowire(ChangeTradePostName::class),
        CreateLicense::ACTION_IDENTIFIER => autowire(CreateLicense::class),
        CreateOffer::ACTION_IDENTIFIER => autowire(CreateOffer::class),
        TakeOffer::ACTION_IDENTIFIER => autowire(TakeOffer::class),
        TransferGoods::ACTION_IDENTIFIER => autowire(TransferGoods::class)
    ],
    'TRADE_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        Overview::VIEW_IDENTIFIER => autowire(Overview::class),
        ShowAccounts::VIEW_IDENTIFIER => autowire(ShowAccounts::class),
        ShowLicenseInfo::VIEW_IDENTIFIER => autowire(ShowLicenseInfo::class),
        ShowLicenseList::VIEW_IDENTIFIER => autowire(ShowLicenseList::class),
        ShowLicenseMenu::VIEW_IDENTIFIER => autowire(ShowLicenseMenu::class),
        ShowOfferMenu::VIEW_IDENTIFIER => autowire(ShowOfferMenu::class),
        ShowTransferMenu::VIEW_IDENTIFIER => autowire(ShowTransferMenu::class),
        ShowOfferMenuNewOffer::VIEW_IDENTIFIER => autowire(ShowOfferMenuNewOffer::class),
        ShowTakeOffer::VIEW_IDENTIFIER => autowire(ShowTakeOffer::class),
        ShowTradePostInfo::VIEW_IDENTIFIER => autowire(ShowTradePostInfo::class),
        ShowOfferGood::VIEW_IDENTIFIER => autowire(ShowOfferGood::class),
        ShowShoutBox::VIEW_IDENTIFIER => autowire(ShowShoutBox::class),
        ShowShoutBoxList::VIEW_IDENTIFIER => autowire(ShowShoutBoxList::class),
        ShowSearchDemand::VIEW_IDENTIFIER => autowire(ShowSearchDemand::class),
        ShowSearchOffer::VIEW_IDENTIFIER => autowire(ShowSearchOffer::class),
        ShowSearchBoth::VIEW_IDENTIFIER => autowire(ShowSearchBoth::class),
        ShowBasicTrade::VIEW_IDENTIFIER => autowire(ShowBasicTrade::class),
        ShowShiplist::VIEW_IDENTIFIER => autowire(ShowShiplist::class)
    ],
];
