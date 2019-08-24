<?php

declare(strict_types=1);

namespace Stu\Module\Communication;

use Stu\Control\GameController;
use Stu\Control\IntermediateController;
use Stu\Lib\SessionInterface;
use Stu\Module\Communication\Action\AddContact\AddContact;
use Stu\Module\Communication\Action\AddContact\AddContactRequest;
use Stu\Module\Communication\Action\AddContact\AddContactRequestInterface;
use Stu\Module\Communication\Action\DeleteAllContacts\DeleteAllContacts;
use Stu\Module\Communication\Action\DeleteAllIgnores\DeleteAllIgnores;
use Stu\Module\Communication\Action\DeleteContacts\DeleteContacts;
use Stu\Module\Communication\Action\DeleteContacts\DeleteContactsRequest;
use Stu\Module\Communication\Action\DeleteContacts\DeleteContactsRequestInterface;
use Stu\Module\Communication\Action\DeleteIgnores\DeleteIgnores;
use Stu\Module\Communication\Action\DeleteIgnores\DeleteIgnoresRequest;
use Stu\Module\Communication\Action\DeleteIgnores\DeleteIgnoresRequestInterface;
use Stu\Module\Communication\Action\IgnoreUser\IgnoreUser;
use Stu\Module\Communication\Action\IgnoreUser\IgnoreUserRequest;
use Stu\Module\Communication\Action\IgnoreUser\IgnoreUserRequestInterface;
use Stu\Module\Communication\Action\SetKnMark\SetKnMark;
use Stu\Module\Communication\Action\SetKnMark\SetKnMarkRequest;
use Stu\Module\Communication\Action\SetKnMark\SetKnMarkRequestInterface;
use Stu\Module\Communication\Action\SwitchContactMode\SwitchContactMode;
use Stu\Module\Communication\Action\SwitchContactMode\SwitchContactModeRequest;
use Stu\Module\Communication\Action\SwitchContactMode\SwitchContactModeRequestInterface;
use Stu\Module\Communication\View\Overview\Overview;
use Stu\Module\Communication\View\Overview\OverviewRequest;
use Stu\Module\Communication\View\Overview\OverviewRequestInterface;
use Stu\Module\Communication\View\ShowContactList\ShowContactList;
use Stu\Module\Communication\View\ShowContactMode\ShowContactMode;
use Stu\Module\Communication\View\ShowContactModeSwitch\ShowContactModeSwitch;
use Stu\Module\Communication\View\ShowContactModeSwitch\ShowContactModeSwitchRequest;
use Stu\Module\Communication\View\ShowContactModeSwitch\ShowContactModeSwitchRequestInterface;
use Stu\Module\Communication\View\ShowCreatePlot\ShowCreatePlot;
use Stu\Module\Communication\View\ShowEditKn\ShowEditKn;
use Stu\Module\Communication\View\ShowEditKn\ShowEditKnRequest;
use Stu\Module\Communication\View\ShowEditKn\ShowEditKnRequestInterface;
use Stu\Module\Communication\View\ShowEditPlot\ShowEditPlot;
use Stu\Module\Communication\View\ShowEditPlot\ShowEditPlotRequest;
use Stu\Module\Communication\View\ShowEditPlot\ShowEditPlotRequestInterface;
use Stu\Module\Communication\View\ShowEditPmCategory\ShowEditCategoryRequest;
use Stu\Module\Communication\View\ShowEditPmCategory\ShowEditCategoryRequestInterface;
use Stu\Module\Communication\View\ShowEditPmCategory\ShowEditPmCategory;
use Stu\Module\Communication\View\ShowIgnore\ShowIgnore;
use Stu\Module\Communication\View\ShowIgnoreList\ShowIgnoreList;
use Stu\Module\Communication\View\ShowKnComments\ShowKnComments;
use Stu\Module\Communication\View\ShowKnComments\ShowKnCommentsRequest;
use Stu\Module\Communication\View\ShowKnComments\ShowKnCommentsRequestInterface;
use Stu\Module\Communication\View\ShowKnPlot\ShowKnPlot;
use Stu\Module\Communication\View\ShowKnPlot\ShowKnPlotRequest;
use Stu\Module\Communication\View\ShowKnPlot\ShowKnPlotRequestInterface;
use Stu\Module\Communication\View\ShowNewPm\ShowNewPm;
use Stu\Module\Communication\View\ShowNewPmCategory\ShowNewPmCategory;
use Stu\Module\Communication\View\ShowPlotKn\ShowPlotKn;
use Stu\Module\Communication\View\ShowPlotKn\ShowPlotKnRequest;
use Stu\Module\Communication\View\ShowPlotKn\ShowPlotKnRequestInterface;
use Stu\Module\Communication\View\ShowPlotList\ShowPlotList;
use Stu\Module\Communication\View\ShowPmCategory\ShowPmCategory;
use Stu\Module\Communication\View\ShowPmCategory\ShowPmCategoryRequest;
use Stu\Module\Communication\View\ShowPmCategory\ShowPmCategoryRequestInterface;
use Stu\Module\Communication\View\ShowPmCategoryList\ShowPmCategoryList;
use Stu\Module\Communication\View\ShowUserPlotList\ShowUserPlotList;
use Stu\Module\Communication\View\ShowWriteKn\ShowWriteKn;
use Stu\Module\Communication\View\ShowWritePm\ShowWritePm;
use Stu\Module\Communication\View\ShowWritePm\ShowWritePmRequest;
use Stu\Module\Communication\View\ShowWritePm\ShowWritePmRequestInterface;
use Stu\Module\Communication\View\ShowWriteQuickPm\ShowWriteQuickPm;
use Stu\Module\Communication\View\ShowWriteQuickPm\ShowWriteQuickPmRequest;
use Stu\Module\Communication\View\ShowWriteQuickPm\ShowWriteQuickPmRequestInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    OverviewRequestInterface::class => autowire(OverviewRequest::class),
    SetKnMarkRequestInterface::class => autowire(SetKnMarkRequest::class),
    ShowWriteQuickPmRequestInterface::class => autowire(ShowWriteQuickPmRequest::class),
    ShowWritePmRequestInterface::class => autowire(ShowWritePmRequest::class),
    ShowKnCommentsRequestInterface::class => autowire(ShowKnCommentsRequest::class),
    ShowPmCategoryRequestInterface::class => autowire(ShowPmCategoryRequest::class),
    ShowEditCategoryRequestInterface::class => autowire(ShowEditCategoryRequest::class),
    ShowKnPlotRequestInterface::class => autowire(ShowKnPlotRequest::class),
    ShowPlotKnRequestInterface::class => autowire(ShowPlotKnRequest::class),
    ShowEditPlotRequestInterface::class => autowire(ShowEditPlotRequest::class),
    ShowEditKnRequestInterface::class => autowire(ShowEditKnRequest::class),
    ShowContactModeSwitchRequestInterface::class => autowire(ShowContactModeSwitchRequest::class),
    SwitchContactModeRequestInterface::class => autowire(SwitchContactModeRequest::class),
    AddContactRequestInterface::class => autowire(AddContactRequest::class),
    DeleteContactsRequestInterface::class => autowire(DeleteContactsRequest::class),
    IgnoreUserRequestInterface::class => autowire(IgnoreUserRequest::class),
    DeleteIgnoresRequestInterface::class => autowire(DeleteIgnoresRequest::class),
    IntermediateController::TYPE_COMMUNICATION => create(IntermediateController::class)
        ->constructor(
            get(SessionInterface::class),
            get(SessionStringRepositoryInterface::class),
            [
                SetKnMark::ACTION_IDENTIFIER => autowire(SetKnMark::class),
                SwitchContactMode::ACTION_IDENTIFIER => autowire(SwitchContactMode::class),
                AddContact::ACTION_IDENTIFIER => autowire(AddContact::class),
                DeleteContacts::ACTION_IDENTIFIER => autowire(DeleteContacts::class),
                DeleteAllContacts::ACTION_IDENTIFIER => autowire(DeleteAllContacts::class),
                IgnoreUser::ACTION_IDENTIFIER => autowire(IgnoreUser::class),
                DeleteAllIgnores::ACTION_IDENTIFIER => autowire(DeleteAllIgnores::class),
                DeleteIgnores::ACTION_IDENTIFIER => autowire(DeleteIgnores::class),
            ],
            [
                GameController::DEFAULT_VIEW => autowire(Overview::class),
                ShowNewPm::VIEW_IDENTIFIER => autowire(ShowNewPm::class),
                ShowWriteQuickPm::VIEW_IDENTIFIER => autowire(ShowWriteQuickPm::class),
                ShowWritePm::VIEW_IDENTIFIER => autowire(ShowWritePm::class),
                ShowKnComments::VIEW_IDENTIFIER => autowire(ShowKnComments::class),
                ShowContactList::VIEW_IDENTIFIER => autowire(ShowContactList::class),
                ShowIgnoreList::VIEW_IDENTIFIER => autowire(ShowIgnoreList::class),
                ShowPmCategory::VIEW_IDENTIFIER => autowire(ShowPmCategory::class),
                ShowNewPmCategory::VIEW_IDENTIFIER => autowire(ShowNewPmCategory::class),
                ShowPmCategoryList::VIEW_IDENTIFIER => autowire(ShowPmCategoryList::class),
                ShowEditPmCategory::VIEW_IDENTIFIER => autowire(ShowEditPmCategory::class),
                ShowKnPlot::VIEW_IDENTIFIER => autowire(ShowKnPlot::class),
                ShowPlotKn::VIEW_IDENTIFIER => autowire(ShowPlotKn::class),
                ShowPlotList::VIEW_IDENTIFIER => autowire(ShowPlotList::class),
                ShowUserPlotList::VIEW_IDENTIFIER => autowire(ShowUserPlotList::class),
                ShowCreatePlot::VIEW_IDENTIFIER => autowire(ShowCreatePlot::class),
                ShowEditPlot::VIEW_IDENTIFIER => autowire(ShowEditPlot::class),
                ShowWriteKn::VIEW_IDENTIFIER => autowire(ShowWriteKn::class),
                ShowEditKn::VIEW_IDENTIFIER => autowire(ShowEditKn::class),
                ShowIgnore::VIEW_IDENTIFIER => autowire(ShowIgnore::class),
                ShowContactModeSwitch::VIEW_IDENTIFIER => autowire(ShowContactModeSwitch::class),
                ShowContactMode::VIEW_IDENTIFIER => autowire(ShowContactMode::class),
            ]
        ),
];