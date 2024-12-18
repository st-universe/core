<?php

declare(strict_types=1);

namespace Stu\Module\Message;

use Stu\Module\Control\GameController;
use Stu\Module\Game\View\Overview\Overview;
use Stu\Module\Message\Action\AddContact\AddContact;
use Stu\Module\Message\Action\AddContact\AddContactRequest;
use Stu\Module\Message\Action\AddContact\AddContactRequestInterface;
use Stu\Module\Message\Action\AddPmCategory\AddPmCategory;
use Stu\Module\Message\Action\AddPmCategory\AddPmCategoryRequest;
use Stu\Module\Message\Action\AddPmCategory\AddPmCategoryRequestInterface;
use Stu\Module\Message\Action\DeleteAllContacts\DeleteAllContacts;
use Stu\Module\Message\Action\DeleteAllIgnores\DeleteAllIgnores;
use Stu\Module\Message\Action\DeleteAllPms\DeleteAllPms;
use Stu\Module\Message\Action\DeleteAllPms\DeleteAllPmsRequest;
use Stu\Module\Message\Action\DeleteAllPms\DeleteAllPmsRequestInterface;
use Stu\Module\Message\Action\DeleteContacts\DeleteContacts;
use Stu\Module\Message\Action\DeleteContacts\DeleteContactsRequest;
use Stu\Module\Message\Action\DeleteContacts\DeleteContactsRequestInterface;
use Stu\Module\Message\Action\DeleteIgnores\DeleteIgnores;
use Stu\Module\Message\Action\DeleteIgnores\DeleteIgnoresRequest;
use Stu\Module\Message\Action\DeleteIgnores\DeleteIgnoresRequestInterface;
use Stu\Module\Message\Action\DeletePmCategory\DeletePmCategory;
use Stu\Module\Message\Action\DeletePmCategory\DeletePmCategoryRequest;
use Stu\Module\Message\Action\DeletePmCategory\DeletePmCategoryRequestInterface;
use Stu\Module\Message\Action\DeletePms\DeletePms;
use Stu\Module\Message\Action\DeletePms\DeletePmsRequest;
use Stu\Module\Message\Action\DeletePms\DeletePmsRequestInterface;
use Stu\Module\Message\Action\EditContactComment\EditContactComment;
use Stu\Module\Message\Action\EditContactComment\EditContactCommentRequest;
use Stu\Module\Message\Action\EditContactComment\EditContactCommentRequestInterface;
use Stu\Module\Message\Action\EditPmCategory\EditPmCategory;
use Stu\Module\Message\Action\EditPmCategory\EditPmCategoryRequest;
use Stu\Module\Message\Action\EditPmCategory\EditPmCategoryRequestInterface;
use Stu\Module\Message\Action\IgnoreUser\IgnoreUser;
use Stu\Module\Message\Action\IgnoreUser\IgnoreUserRequest;
use Stu\Module\Message\Action\IgnoreUser\IgnoreUserRequestInterface;
use Stu\Module\Message\Action\MovePm\MovePm;
use Stu\Module\Message\Action\MovePm\MovePmRequest;
use Stu\Module\Message\Action\MovePm\MovePmRequestInterface;
use Stu\Module\Message\Action\SortPmCategories\SortPmCategories;
use Stu\Module\Message\Action\SortPmCategories\SortPmCategoriesRequest;
use Stu\Module\Message\Action\SortPmCategories\SortPmCategoriesRequestInterface;
use Stu\Module\Message\Action\SwitchContactMode\SwitchContactMode;
use Stu\Module\Message\Action\SwitchContactMode\SwitchContactModeRequest;
use Stu\Module\Message\Action\SwitchContactMode\SwitchContactModeRequestInterface;
use Stu\Module\Message\Action\WritePm\WritePm;
use Stu\Module\Message\Action\WritePm\WritePmRequest;
use Stu\Module\Message\Action\WritePm\WritePmRequestInterface;
use Stu\Module\Message\Lib\DistributedMessageSender;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageSender;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageUiFactory;
use Stu\Module\Message\Lib\PrivateMessageUiFactoryInterface;
use Stu\Module\Message\View\Noop\Noop;
use Stu\Module\Message\View\ShowContactList\ShowContactList;
use Stu\Module\Message\View\ShowContactMode\ShowContactMode;
use Stu\Module\Message\View\ShowContactModeSwitch\ShowContactModeSwitch;
use Stu\Module\Message\View\ShowContactModeSwitch\ShowContactModeSwitchRequest;
use Stu\Module\Message\View\ShowContactModeSwitch\ShowContactModeSwitchRequestInterface;
use Stu\Module\Message\View\ShowEditPmCategory\ShowEditCategoryRequest;
use Stu\Module\Message\View\ShowEditPmCategory\ShowEditCategoryRequestInterface;
use Stu\Module\Message\View\ShowEditPmCategory\ShowEditPmCategory;
use Stu\Module\Message\View\ShowIgnore\ShowIgnore;
use Stu\Module\Message\View\ShowIgnoreList\ShowIgnoreList;
use Stu\Module\Message\View\ShowNewPmCategory\ShowNewPmCategory;
use Stu\Module\Message\View\ShowPmCategoryList\ShowPmCategoryList;
use Stu\Module\Message\View\ShowWritePm\ShowWritePm;
use Stu\Module\Message\View\ShowWritePm\ShowWritePmRequest;
use Stu\Module\Message\View\ShowWritePm\ShowWritePmRequestInterface;
use Stu\Module\Message\View\ShowWriteQuickPm\ShowWriteQuickPm;

use function DI\autowire;

return [
    DistributedMessageSenderInterface::class => autowire(DistributedMessageSender::class),
    PrivateMessageSenderInterface::class => autowire(PrivateMessageSender::class),
    ShowWritePmRequestInterface::class => autowire(ShowWritePmRequest::class),
    ShowEditCategoryRequestInterface::class => autowire(ShowEditCategoryRequest::class),
    ShowContactModeSwitchRequestInterface::class => autowire(ShowContactModeSwitchRequest::class),
    SwitchContactModeRequestInterface::class => autowire(SwitchContactModeRequest::class),
    AddContactRequestInterface::class => autowire(AddContactRequest::class),
    DeleteContactsRequestInterface::class => autowire(DeleteContactsRequest::class),
    IgnoreUserRequestInterface::class => autowire(IgnoreUserRequest::class),
    DeleteIgnoresRequestInterface::class => autowire(DeleteIgnoresRequest::class),
    SortPmCategoriesRequestInterface::class => autowire(SortPmCategoriesRequest::class),
    AddPmCategoryRequestInterface::class => autowire(AddPmCategoryRequest::class),
    EditPmCategoryRequestInterface::class => autowire(EditPmCategoryRequest::class),
    DeletePmCategoryRequestInterface::class => autowire(DeletePmCategoryRequest::class),
    DeletePmsRequestInterface::class => autowire(DeletePmsRequest::class),
    DeleteAllPmsRequestInterface::class => autowire(DeleteAllPmsRequest::class),
    MovePmRequestInterface::class => autowire(MovePmRequest::class),
    WritePmRequestInterface::class => autowire(WritePmRequest::class),
    EditContactCommentRequestInterface::class => autowire(EditContactCommentRequest::class),
    'PM_ACTIONS' => [
        SwitchContactMode::ACTION_IDENTIFIER => autowire(SwitchContactMode::class),
        AddContact::ACTION_IDENTIFIER => autowire(AddContact::class),
        DeleteContacts::ACTION_IDENTIFIER => autowire(DeleteContacts::class),
        DeleteAllContacts::ACTION_IDENTIFIER => autowire(DeleteAllContacts::class),
        IgnoreUser::ACTION_IDENTIFIER => autowire(IgnoreUser::class),
        DeleteAllIgnores::ACTION_IDENTIFIER => autowire(DeleteAllIgnores::class),
        DeleteIgnores::ACTION_IDENTIFIER => autowire(DeleteIgnores::class),
        SortPmCategories::ACTION_IDENTIFIER => autowire(SortPmCategories::class),
        AddPmCategory::ACTION_IDENTIFIER => autowire(AddPmCategory::class),
        EditPmCategory::ACTION_IDENTIFIER => autowire(EditPmCategory::class),
        DeletePmCategory::ACTION_IDENTIFIER => autowire(DeletePmCategory::class),
        DeletePms::ACTION_IDENTIFIER => autowire(DeletePms::class),
        DeleteAllPms::ACTION_IDENTIFIER => autowire(DeleteAllPms::class),
        MovePm::ACTION_IDENTIFIER => autowire(MovePm::class),
        WritePm::ACTION_IDENTIFIER => autowire(WritePm::class),
        EditContactComment::ACTION_IDENTIFIER => autowire(EditContactComment::class),
    ],
    'PM_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        ShowWriteQuickPm::VIEW_IDENTIFIER => autowire(ShowWriteQuickPm::class),
        ShowWritePm::VIEW_IDENTIFIER => autowire(ShowWritePm::class),
        ShowContactList::VIEW_IDENTIFIER => autowire(ShowContactList::class),
        ShowIgnoreList::VIEW_IDENTIFIER => autowire(ShowIgnoreList::class),
        ShowNewPmCategory::VIEW_IDENTIFIER => autowire(ShowNewPmCategory::class),
        ShowPmCategoryList::VIEW_IDENTIFIER => autowire(ShowPmCategoryList::class),
        ShowEditPmCategory::VIEW_IDENTIFIER => autowire(ShowEditPmCategory::class),
        ShowIgnore::VIEW_IDENTIFIER => autowire(ShowIgnore::class),
        ShowContactModeSwitch::VIEW_IDENTIFIER => autowire(ShowContactModeSwitch::class),
        ShowContactMode::VIEW_IDENTIFIER => autowire(ShowContactMode::class),
        Noop::VIEW_IDENTIFIER => autowire(Noop::class),
    ],
    PrivateMessageUiFactoryInterface::class => autowire(PrivateMessageUiFactory::class),
];
