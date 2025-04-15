<?php

declare(strict_types=1);

namespace Stu\Module\Communication;

use Stu\Component\Communication\Kn\KnBbCodeDefinitionSet;
use Stu\Module\Communication\Action\AddKnPlotMember\AddKnPlotMember;
use Stu\Module\Communication\Action\AddKnPlotMember\AddKnPlotMemberRequest;
use Stu\Module\Communication\Action\AddKnPlotMember\AddKnPlotMemberRequestInterface;
use Stu\Module\Communication\Action\AddKnPost\AddKnPost;
use Stu\Module\Communication\Action\AddKnPost\AddKnPostRequest;
use Stu\Module\Communication\Action\AddKnPost\AddKnPostRequestInterface;
use Stu\Module\Communication\Action\AddKnPostToPlot\AddKnPostToPlot;
use Stu\Module\Communication\Action\ApplyKnPostToPlot\ApplyKnPostToPlot;
use Stu\Module\Communication\Action\CreateKnPlot\CreateKnPlot;
use Stu\Module\Communication\Action\CreateKnPlot\CreateKnPlotRequest;
use Stu\Module\Communication\Action\CreateKnPlot\CreateKnPlotRequestInterface;
use Stu\Module\Communication\Action\DeleteKnComment\DeleteKnComment;
use Stu\Module\Communication\Action\DeleteKnComment\DeleteKnCommentRequest;
use Stu\Module\Communication\Action\DeleteKnComment\DeleteKnCommentRequestInterface;
use Stu\Module\Communication\Action\DeleteKnPlotMember\DeleteKnPlotMember;
use Stu\Module\Communication\Action\DeleteKnPlotMember\DeleteKnPlotMemberRequest;
use Stu\Module\Communication\Action\DeleteKnPlotMember\DeleteKnPlotMemberRequestInterface;
use Stu\Module\Communication\Action\DeleteKnPost\DeleteKnPost;
use Stu\Module\Communication\Action\DeleteKnPost\DeleteKnPostRequest;
use Stu\Module\Communication\Action\DeleteKnPost\DeleteKnPostRequestInterface;
use Stu\Module\Communication\Action\EditKnPlot\EditKnPlot;
use Stu\Module\Communication\Action\EditKnPlot\EditKnPlotRequest;
use Stu\Module\Communication\Action\EditKnPlot\EditKnPlotRequestInterface;
use Stu\Module\Communication\Action\EditKnPost\EditKnPost;
use Stu\Module\Communication\Action\EditKnPost\EditKnPostRequest;
use Stu\Module\Communication\Action\EditKnPost\EditKnPostRequestInterface;
use Stu\Module\Communication\Action\EndKnPlot\EndKnPlot;
use Stu\Module\Communication\Action\EndKnPlot\EndKnPlotRequest;
use Stu\Module\Communication\Action\EndKnPlot\EndKnPlotRequestInterface;
use Stu\Module\Communication\Action\KnPostPreview\KnPostPreview;
use Stu\Module\Communication\Action\PostKnComment\PostKnComment;
use Stu\Module\Communication\Action\PostKnComment\PostKnCommentRequest;
use Stu\Module\Communication\Action\PostKnComment\PostKnCommentRequestInterface;
use Stu\Module\Communication\Action\RateKnPost\RateKnPost;
use Stu\Module\Communication\Action\RateKnPost\RateKnPostRequest;
use Stu\Module\Communication\Action\RateKnPost\RateKnPostRequestInterface;
use Stu\Module\Communication\Action\SetKnMark\SetKnMark;
use Stu\Module\Communication\Action\SetKnMark\SetKnMarkRequest;
use Stu\Module\Communication\Action\SetKnMark\SetKnMarkRequestInterface;
use Stu\Module\Communication\Lib\NewKnPostNotificator;
use Stu\Module\Communication\View\ShowCreatePlot\ShowCreatePlot;
use Stu\Module\Communication\View\ShowEditKn\ShowEditKn;
use Stu\Module\Communication\View\ShowEditKn\ShowEditKnRequest;
use Stu\Module\Communication\View\ShowEditKn\ShowEditKnRequestInterface;
use Stu\Module\Communication\View\ShowEditPlot\ShowEditPlot;
use Stu\Module\Communication\View\ShowEditPlot\ShowEditPlotRequest;
use Stu\Module\Communication\View\ShowEditPlot\ShowEditPlotRequestInterface;
use Stu\Module\Communication\View\ShowKnCharacter\ShowKnCharacter;
use Stu\Module\Communication\View\ShowKnCharacter\ShowKnCharacterRequest;
use Stu\Module\Communication\View\ShowKnCharacter\ShowKnCharacterRequestInterface;
use Stu\Module\Communication\View\ShowKnComments\ShowKnComments;
use Stu\Module\Communication\View\ShowKnComments\ShowKnCommentsRequest;
use Stu\Module\Communication\View\ShowKnComments\ShowKnCommentsRequestInterface;
use Stu\Module\Communication\View\ShowKnPlot\ShowKnPlot;
use Stu\Module\Communication\View\ShowKnPlot\ShowKnPlotRequest;
use Stu\Module\Communication\View\ShowKnPlot\ShowKnPlotRequestInterface;
use Stu\Module\Communication\View\ShowKnRating\ShowKnRating;
use Stu\Module\Communication\View\ShowPlotList\ShowPlotList;
use Stu\Module\Communication\View\ShowSearchResult\ShowPostIdSearchResult;
use Stu\Module\Communication\View\ShowSearchResult\ShowPostSearchResult;
use Stu\Module\Communication\View\ShowSearchResult\ShowSearchResultRequest;
use Stu\Module\Communication\View\ShowSearchResult\ShowSearchResultRequestInterface;
use Stu\Module\Communication\View\ShowSearchResult\ShowUserSearchResult;
use Stu\Module\Communication\View\ShowSingleKn\ShowSingleKn;
use Stu\Module\Communication\View\ShowSingleKn\ShowSingleKnRequest;
use Stu\Module\Communication\View\ShowSingleKn\ShowSingleKnRequestInterface;
use Stu\Module\Communication\View\ShowUserPlotList\ShowUserPlotList;
use Stu\Module\Communication\View\ShowWriteKn\ShowWriteKn;
use Stu\Module\Control\GameController;
use Stu\Module\Game\View\Overview\Overview;

use function DI\autowire;

return [
    RateKnPostRequestInterface::class => autowire(RateKnPostRequest::class),
    SetKnMarkRequestInterface::class => autowire(SetKnMarkRequest::class),
    ShowKnCharacterRequestInterface::class => autowire(ShowKnCharacterRequest::class),
    ShowKnCommentsRequestInterface::class => autowire(ShowKnCommentsRequest::class),
    ShowKnPlotRequestInterface::class => autowire(ShowKnPlotRequest::class),
    ShowEditPlotRequestInterface::class => autowire(ShowEditPlotRequest::class),
    ShowEditKnRequestInterface::class => autowire(ShowEditKnRequest::class),
    AddKnPostRequestInterface::class => autowire(AddKnPostRequest::class),
    PostKnCommentRequestInterface::class => autowire(PostKnCommentRequest::class),
    DeleteKnCommentRequestInterface::class => autowire(DeleteKnCommentRequest::class),
    EditKnPostRequestInterface::class => autowire(EditKnPostRequest::class),
    DeleteKnPostRequestInterface::class => autowire(DeleteKnPostRequest::class),
    EditKnPlotRequestInterface::class => autowire(EditKnPlotRequest::class),
    AddKnPlotMemberRequestInterface::class => autowire(AddKnPlotMemberRequest::class),
    DeleteKnPlotMemberRequestInterface::class => autowire(DeleteKnPlotMemberRequest::class),
    CreateKnPlotRequestInterface::class => autowire(CreateKnPlotRequest::class),
    EndKnPlotRequestInterface::class => autowire(EndKnPlotRequest::class),
    ShowSingleKnRequestInterface::class => autowire(ShowSingleKnRequest::class),
    ShowSearchResultRequestInterface::class => autowire(ShowSearchResultRequest::class),
    'COMMUNICATION_ACTIONS' => [
        SetKnMark::ACTION_IDENTIFIER => autowire(SetKnMark::class),
        AddKnPost::ACTION_IDENTIFIER => autowire(AddKnPost::class)
            ->constructorParameter(
                'newKnPostNotificator',
                autowire(NewKnPostNotificator::class)
            ),
        PostKnComment::ACTION_IDENTIFIER => autowire(PostKnComment::class),
        DeleteKnComment::ACTION_IDENTIFIER => autowire(DeleteKnComment::class),
        EditKnPost::ACTION_IDENTIFIER => autowire(EditKnPost::class),
        DeleteKnPost::ACTION_IDENTIFIER => autowire(DeleteKnPost::class),
        EditKnPlot::ACTION_IDENTIFIER => autowire(EditKnPlot::class),
        AddKnPlotMember::ACTION_IDENTIFIER => autowire(AddKnPlotMember::class),
        ApplyKnPostToPlot::ACTION_IDENTIFIER => autowire(ApplyKnPostToPlot::class),
        AddKnPostToPlot::ACTION_IDENTIFIER => autowire(AddKnPostToPlot::class),
        DeleteKnPlotMember::ACTION_IDENTIFIER => autowire(DeleteKnPlotMember::class),
        CreateKnPlot::ACTION_IDENTIFIER => autowire(CreateKnPlot::class),
        EndKnPlot::ACTION_IDENTIFIER => autowire(EndKnPlot::class),
        RateKnPost::ACTION_IDENTIFIER => autowire(RateKnPost::class),
        KnPostPreview::ACTION_IDENTIFIER => autowire(KnPostPreview::class)
    ],
    'COMMUNICATION_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        ShowKnCharacter::VIEW_IDENTIFIER => autowire(ShowKnCharacter::class),
        ShowKnComments::VIEW_IDENTIFIER => autowire(ShowKnComments::class),
        ShowKnPlot::VIEW_IDENTIFIER => autowire(ShowKnPlot::class),
        ShowPlotList::VIEW_IDENTIFIER => autowire(ShowPlotList::class),
        ShowUserPlotList::VIEW_IDENTIFIER => autowire(ShowUserPlotList::class),
        ShowCreatePlot::VIEW_IDENTIFIER => autowire(ShowCreatePlot::class),
        ShowEditPlot::VIEW_IDENTIFIER => autowire(ShowEditPlot::class),
        ShowWriteKn::VIEW_IDENTIFIER => autowire(ShowWriteKn::class),
        ShowEditKn::VIEW_IDENTIFIER => autowire(ShowEditKn::class),
        ShowKnRating::VIEW_IDENTIFIER => autowire(ShowKnRating::class),
        ShowSingleKn::VIEW_IDENTIFIER => autowire(ShowSingleKn::class),
        ShowPostSearchResult::VIEW_IDENTIFIER => autowire(ShowPostSearchResult::class),
        ShowUserSearchResult::VIEW_IDENTIFIER => autowire(ShowUserSearchResult::class),
        ShowPostIdSearchResult::VIEW_IDENTIFIER => autowire(ShowPostIdSearchResult::class)
    ],
];
