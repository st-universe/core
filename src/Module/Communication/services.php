<?php

declare(strict_types=1);

namespace Stu\Module\Communication;

use Stu\Module\Communication\Lib\KnTalFactory;
use Stu\Module\Communication\Lib\KnTalFactoryInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Communication\Action\AddContact\AddContact;
use Stu\Module\Communication\Action\AddContact\AddContactRequest;
use Stu\Module\Communication\Action\AddContact\AddContactRequestInterface;
use Stu\Module\Communication\Action\AddKnPlotMember\AddKnPlotMember;
use Stu\Module\Communication\Action\AddKnPlotMember\AddKnPlotMemberRequest;
use Stu\Module\Communication\Action\AddKnPlotMember\AddKnPlotMemberRequestInterface;
use Stu\Module\Communication\Action\AddKnPost\AddKnPost;
use Stu\Module\Communication\Action\AddKnPost\AddKnPostRequest;
use Stu\Module\Communication\Action\AddKnPost\AddKnPostRequestInterface;
use Stu\Module\Communication\Action\AddPmCategory\AddPmCategory;
use Stu\Module\Communication\Action\AddPmCategory\AddPmCategoryRequest;
use Stu\Module\Communication\Action\AddPmCategory\AddPmCategoryRequestInterface;
use Stu\Module\Communication\Action\CreateKnPlot\CreateKnPlot;
use Stu\Module\Communication\Action\CreateKnPlot\CreateKnPlotRequest;
use Stu\Module\Communication\Action\CreateKnPlot\CreateKnPlotRequestInterface;
use Stu\Module\Communication\Action\DeleteAllContacts\DeleteAllContacts;
use Stu\Module\Communication\Action\DeleteAllIgnores\DeleteAllIgnores;
use Stu\Module\Communication\Action\DeleteAllPms\DeleteAllPms;
use Stu\Module\Communication\Action\DeleteAllPms\DeleteAllPmsRequest;
use Stu\Module\Communication\Action\DeleteAllPms\DeleteAllPmsRequestInterface;
use Stu\Module\Communication\Action\DeleteContacts\DeleteContacts;
use Stu\Module\Communication\Action\DeleteContacts\DeleteContactsRequest;
use Stu\Module\Communication\Action\DeleteContacts\DeleteContactsRequestInterface;
use Stu\Module\Communication\Action\DeleteIgnores\DeleteIgnores;
use Stu\Module\Communication\Action\DeleteIgnores\DeleteIgnoresRequest;
use Stu\Module\Communication\Action\DeleteIgnores\DeleteIgnoresRequestInterface;
use Stu\Module\Communication\Action\DeleteKnComment\DeleteKnComment;
use Stu\Module\Communication\Action\DeleteKnComment\DeleteKnCommentRequest;
use Stu\Module\Communication\Action\DeleteKnComment\DeleteKnCommentRequestInterface;
use Stu\Module\Communication\Action\DeleteKnPlotMember\DeleteKnPlotMember;
use Stu\Module\Communication\Action\DeleteKnPlotMember\DeleteKnPlotMemberRequest;
use Stu\Module\Communication\Action\DeleteKnPlotMember\DeleteKnPlotMemberRequestInterface;
use Stu\Module\Communication\Action\DeleteKnPost\DeleteKnPost;
use Stu\Module\Communication\Action\DeleteKnPost\DeleteKnPostRequest;
use Stu\Module\Communication\Action\DeleteKnPost\DeleteKnPostRequestInterface;
use Stu\Module\Communication\Action\DeletePmCategory\DeletePmCategory;
use Stu\Module\Communication\Action\DeletePmCategory\DeletePmCategoryRequest;
use Stu\Module\Communication\Action\DeletePmCategory\DeletePmCategoryRequestInterface;
use Stu\Module\Communication\Action\DeletePms\DeletePms;
use Stu\Module\Communication\Action\DeletePms\DeletePmsRequest;
use Stu\Module\Communication\Action\DeletePms\DeletePmsRequestInterface;
use Stu\Module\Communication\Action\EditContactComment\EditContactComment;
use Stu\Module\Communication\Action\EditContactComment\EditContactCommentRequest;
use Stu\Module\Communication\Action\EditContactComment\EditContactCommentRequestInterface;
use Stu\Module\Communication\Action\EditKnPlot\EditKnPlot;
use Stu\Module\Communication\Action\EditKnPlot\EditKnPlotRequest;
use Stu\Module\Communication\Action\EditKnPlot\EditKnPlotRequestInterface;
use Stu\Module\Communication\Action\EditKnPost\EditKnPost;
use Stu\Module\Communication\Action\EditKnPost\EditKnPostRequest;
use Stu\Module\Communication\Action\EditKnPost\EditKnPostRequestInterface;
use Stu\Module\Communication\Action\EditPmCategory\EditPmCategory;
use Stu\Module\Communication\Action\EditPmCategory\EditPmCategoryRequest;
use Stu\Module\Communication\Action\EditPmCategory\EditPmCategoryRequestInterface;
use Stu\Module\Communication\Action\EndKnPlot\EndKnPlot;
use Stu\Module\Communication\Action\EndKnPlot\EndKnPlotRequest;
use Stu\Module\Communication\Action\EndKnPlot\EndKnPlotRequestInterface;
use Stu\Module\Communication\Action\IgnoreUser\IgnoreUser;
use Stu\Module\Communication\Action\IgnoreUser\IgnoreUserRequest;
use Stu\Module\Communication\Action\IgnoreUser\IgnoreUserRequestInterface;
use Stu\Module\Communication\Action\MovePm\MovePm;
use Stu\Module\Communication\Action\MovePm\MovePmRequest;
use Stu\Module\Communication\Action\MovePm\MovePmRequestInterface;
use Stu\Module\Communication\Action\PostKnComment\PostKnComment;
use Stu\Module\Communication\Action\PostKnComment\PostKnCommentRequest;
use Stu\Module\Communication\Action\PostKnComment\PostKnCommentRequestInterface;
use Stu\Module\Communication\Action\SetKnMark\SetKnMark;
use Stu\Module\Communication\Action\SetKnMark\SetKnMarkRequest;
use Stu\Module\Communication\Action\SetKnMark\SetKnMarkRequestInterface;
use Stu\Module\Communication\Action\SortPmCategories\SortPmCategories;
use Stu\Module\Communication\Action\SortPmCategories\SortPmCategoriesRequest;
use Stu\Module\Communication\Action\SortPmCategories\SortPmCategoriesRequestInterface;
use Stu\Module\Communication\Action\SwitchContactMode\SwitchContactMode;
use Stu\Module\Communication\Action\SwitchContactMode\SwitchContactModeRequest;
use Stu\Module\Communication\Action\SwitchContactMode\SwitchContactModeRequestInterface;
use Stu\Module\Communication\Action\WritePm\WritePm;
use Stu\Module\Communication\Action\WritePm\WritePmRequest;
use Stu\Module\Communication\Action\WritePm\WritePmRequestInterface;
use Stu\Module\Communication\View\Noop\Noop;
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
use function DI\autowire;

return [
    KnTalFactoryInterface::class => autowire(KnTalFactory::class),
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
    SortPmCategoriesRequestInterface::class => autowire(SortPmCategoriesRequest::class),
    AddPmCategoryRequestInterface::class => autowire(AddPmCategoryRequest::class),
    EditPmCategoryRequestInterface::class => autowire(EditPmCategoryRequest::class),
    DeletePmCategoryRequestInterface::class => autowire(DeletePmCategoryRequest::class),
    DeletePmsRequestInterface::class => autowire(DeletePmsRequest::class),
    DeleteAllPmsRequestInterface::class => autowire(DeleteAllPmsRequest::class),
    MovePmRequestInterface::class => autowire(MovePmRequest::class),
    AddKnPostRequestInterface::class => autowire(AddKnPostRequest::class),
    WritePmRequestInterface::class => autowire(WritePmRequest::class),
    PostKnCommentRequestInterface::class => autowire(PostKnCommentRequest::class),
    DeleteKnCommentRequestInterface::class => autowire(DeleteKnCommentRequest::class),
    EditContactCommentRequestInterface::class => autowire(EditContactCommentRequest::class),
    EditKnPostRequestInterface::class => autowire(EditKnPostRequest::class),
    DeleteKnPostRequestInterface::class => autowire(DeleteKnPostRequest::class),
    EditKnPlotRequestInterface::class => autowire(EditKnPlotRequest::class),
    AddKnPlotMemberRequestInterface::class => autowire(AddKnPlotMemberRequest::class),
    DeleteKnPlotMemberRequestInterface::class => autowire(DeleteKnPlotMemberRequest::class),
    CreateKnPlotRequestInterface::class => autowire(CreateKnPlotRequest::class),
    EndKnPlotRequestInterface::class => autowire(EndKnPlotRequest::class),
    'COMMUNICATION_ACTIONS' => [
        SetKnMark::ACTION_IDENTIFIER => autowire(SetKnMark::class),
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
        AddKnPost::ACTION_IDENTIFIER => autowire(AddKnPost::class),
        WritePm::ACTION_IDENTIFIER => autowire(WritePm::class),
        PostKnComment::ACTION_IDENTIFIER => autowire(PostKnComment::class),
        DeleteKnComment::ACTION_IDENTIFIER => autowire(DeleteKnComment::class),
        EditContactComment::ACTION_IDENTIFIER => autowire(EditContactComment::class),
        EditKnPost::ACTION_IDENTIFIER => autowire(EditKnPost::class),
        DeleteKnPost::ACTION_IDENTIFIER => autowire(DeleteKnPost::class),
        EditKnPlot::ACTION_IDENTIFIER => autowire(EditKnPlot::class),
        AddKnPlotMember::ACTION_IDENTIFIER => autowire(AddKnPlotMember::class),
        DeleteKnPlotMember::ACTION_IDENTIFIER => autowire(DeleteKnPlotMember::class),
        CreateKnPlot::ACTION_IDENTIFIER => autowire(CreateKnPlot::class),
        EndKnPlot::ACTION_IDENTIFIER => autowire(EndKnPlot::class),
    ],
    'COMMUNICATION_VIEWS' => [
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
        Noop::VIEW_IDENTIFIER => autowire(Noop::class),
    ]
];