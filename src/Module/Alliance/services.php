<?php

declare(strict_types=1);

namespace Stu\Module\Alliance;

use Stu\Module\Alliance\Action\DemotePlayer\DemotePlayerRequest;
use Stu\Module\Alliance\Action\AcceptApplication\AcceptApplication;
use Stu\Module\Alliance\Action\AcceptApplication\AcceptApplicationRequest;
use Stu\Module\Alliance\Action\AcceptApplication\AcceptApplicationRequestInterface;
use Stu\Module\Alliance\Action\AcceptOffer\AcceptOffer;
use Stu\Module\Alliance\Action\AcceptOffer\AcceptOfferRequest;
use Stu\Module\Alliance\Action\AcceptOffer\AcceptOfferRequestInterface;
use Stu\Module\Alliance\Action\AddBoard\AddBoard;
use Stu\Module\Alliance\Action\AddBoard\AddBoardRequest;
use Stu\Module\Alliance\Action\AddBoard\AddBoardRequestInterface;
use Stu\Module\Alliance\Action\CancelContract\CancelContract;
use Stu\Module\Alliance\Action\CancelContract\CancelContractRequest;
use Stu\Module\Alliance\Action\CancelContract\CancelContractRequestInterface;
use Stu\Module\Alliance\Action\CancelOffer\CancelOffer;
use Stu\Module\Alliance\Action\CancelOffer\CancelOfferRequest;
use Stu\Module\Alliance\Action\CancelOffer\CancelOfferRequestInterface;
use Stu\Module\Alliance\Action\ChangeAvatar\ChangeAvatar;
use Stu\Module\Alliance\Action\CreateAlliance\CreateAlliance;
use Stu\Module\Alliance\Action\CreateAlliance\CreateAllianceRequest;
use Stu\Module\Alliance\Action\CreateAlliance\CreateAllianceRequestInterface;
use Stu\Module\Alliance\Action\CreatePost\CreatePost;
use Stu\Module\Alliance\Action\CreatePost\CreatePostRequest;
use Stu\Module\Alliance\Action\CreatePost\CreatePostRequestInterface;
use Stu\Module\Alliance\Action\CreateRelation\CreateRelation;
use Stu\Module\Alliance\Action\CreateRelation\CreateRelationRequest;
use Stu\Module\Alliance\Action\CreateRelation\CreateRelationRequestInterface;
use Stu\Module\Alliance\Action\CreateTopic\CreateTopic;
use Stu\Module\Alliance\Action\CreateTopic\CreateTopicRequest;
use Stu\Module\Alliance\Action\CreateTopic\CreateTopicRequestInterface;
use Stu\Module\Alliance\Action\DeclineApplication\DeclineApplication;
use Stu\Module\Alliance\Action\DeclineApplication\DeclineApplicationRequest;
use Stu\Module\Alliance\Action\DeclineApplication\DeclineApplicationRequestInterface;
use Stu\Module\Alliance\Action\DeclineOffer\DeclineOffer;
use Stu\Module\Alliance\Action\DeleteAlliance\DeleteAlliance;
use Stu\Module\Alliance\Action\DeleteAvatar\DeleteAvatar;
use Stu\Module\Alliance\Action\DeleteBoard\DeleteBoard;
use Stu\Module\Alliance\Action\DeleteBoard\DeleteBoardRequest;
use Stu\Module\Alliance\Action\DeleteBoard\DeleteBoardRequestInterface;
use Stu\Module\Alliance\Action\DeletePost\DeletePost;
use Stu\Module\Alliance\Action\DeleteTopic\DeleteTopic;
use Stu\Module\Alliance\Action\DeleteTopic\DeleteTopicRequest;
use Stu\Module\Alliance\Action\DeleteTopic\DeleteTopicRequestInterface;
use Stu\Module\Alliance\Action\DemotePlayer\DemotePlayer;
use Stu\Module\Alliance\Action\EditDetails\EditDetails;
use Stu\Module\Alliance\Action\EditDetails\EditDetailsRequest;
use Stu\Module\Alliance\Action\EditDetails\EditDetailsRequestInterface;
use Stu\Module\Alliance\Action\EditPost\EditPost;
use Stu\Module\Alliance\Action\KickPlayer\KickPlayer;
use Stu\Module\Alliance\Action\KickPlayer\KickPlayerRequest;
use Stu\Module\Alliance\Action\KickPlayer\KickPlayerRequestInterface;
use Stu\Module\Alliance\Action\Leave\Leave;
use Stu\Module\Alliance\Action\PromotePlayer\PromotePlayer;
use Stu\Module\Alliance\Action\PromotePlayer\PromotePlayerRequest;
use Stu\Module\Alliance\Action\PromotePlayer\PromotePlayerRequestInterface;
use Stu\Module\Alliance\Action\RenameBoard\RenameBoard;
use Stu\Module\Alliance\Action\RenameBoard\RenameBoardRequest;
use Stu\Module\Alliance\Action\RenameBoard\RenameBoardRequestInterface;
use Stu\Module\Alliance\Action\RenameTopic\RenameTopic;
use Stu\Module\Alliance\Action\RenameTopic\RenameTopicRequest;
use Stu\Module\Alliance\Action\RenameTopic\RenameTopicRequestInterface;
use Stu\Module\Alliance\Action\SetTopicSticky\SetTopicSticky;
use Stu\Module\Alliance\Action\SetTopicSticky\SetTopicStickyRequest;
use Stu\Module\Alliance\Action\SetTopicSticky\SetTopicStickyRequestInterface;
use Stu\Module\Alliance\Action\Signup\Signup;
use Stu\Module\Alliance\Action\Signup\SignupRequest;
use Stu\Module\Alliance\Action\Signup\SignupRequestInterface;
use Stu\Module\Alliance\Action\SuggestPeace\SuggestPeace;
use Stu\Module\Alliance\Action\SuggestPeace\SuggestPeaceRequest;
use Stu\Module\Alliance\Action\SuggestPeace\SuggestPeaceRequestInterface;
use Stu\Module\Alliance\Action\UnsetTopicSticky\UnsetTopicSticky;
use Stu\Module\Alliance\Action\UnsetTopicSticky\UnsetTopicStickyRequest;
use Stu\Module\Alliance\Action\UnsetTopicSticky\UnsetTopicStickyRequestInterface;
use Stu\Module\Alliance\Lib\AllianceActionManager;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceUiFactory;
use Stu\Module\Alliance\Lib\AllianceUiFactoryInterface;
use Stu\Module\Alliance\View\Applications\Applications;
use Stu\Module\Alliance\View\Board\Board;
use Stu\Module\Alliance\View\Board\BoardRequest;
use Stu\Module\Alliance\View\Board\BoardRequestInterface;
use Stu\Module\Alliance\View\Boards\Boards;
use Stu\Module\Alliance\View\BoardSettings\BoardSettings;
use Stu\Module\Alliance\View\BoardSettings\BoardSettingsRequest;
use Stu\Module\Alliance\View\BoardSettings\BoardSettingsRequestInterface;
use Stu\Module\Alliance\View\Create\Create;
use Stu\Module\Alliance\View\Diplomatic\DiplomaticRelations;
use Stu\Module\Alliance\View\Edit\Edit;
use Stu\Module\Alliance\View\Management\Management;
use Stu\Module\Alliance\View\NewPost\NewPost;
use Stu\Module\Alliance\View\NewPost\NewPostRequest;
use Stu\Module\Alliance\View\NewPost\NewPostRequestInterface;
use Stu\Module\Alliance\View\NewTopic\NewTopic;
use Stu\Module\Alliance\View\NewTopic\NewTopicRequest;
use Stu\Module\Alliance\View\NewTopic\NewTopicRequestInterface;
use Stu\Module\Alliance\View\Relations\Relations;
use Stu\Module\Alliance\View\ShowEditPost\ShowEditPost;
use Stu\Module\Alliance\View\ShowMemberRumpInfo\ShowMemberRumpInfo;
use Stu\Module\Alliance\View\Topic\Topic;
use Stu\Module\Alliance\View\Topic\TopicRequest;
use Stu\Module\Alliance\View\Topic\TopicRequestInterface;
use Stu\Module\Alliance\View\TopicSettings\TopicSettings;
use Stu\Module\Alliance\View\TopicSettings\TopicSettingsRequest;
use Stu\Module\Alliance\View\TopicSettings\TopicSettingsRequestInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Game\View\Overview\Overview;

use function DI\autowire;

return [
    AllianceActionManagerInterface::class => autowire(AllianceActionManager::class),
    AddBoardRequestInterface::class => autowire(AddBoardRequest::class),
    NewTopicRequestInterface::class => autowire(NewTopicRequest::class),
    BoardRequestInterface::class => autowire(BoardRequest::class),
    CreateTopicRequestInterface::class => autowire(CreateTopicRequest::class),
    TopicRequestInterface::class => autowire(TopicRequest::class),
    NewPostRequestInterface::class => autowire(NewPostRequest::class),
    CreatePostRequestInterface::class => autowire(CreatePostRequest::class),
    RenameTopicRequestInterface::class => autowire(RenameTopicRequest::class),
    TopicSettingsRequestInterface::class => autowire(TopicSettingsRequest::class),
    BoardSettingsRequestInterface::class => autowire(BoardSettingsRequest::class),
    RenameBoardRequestInterface::class => autowire(RenameBoardRequest::class),
    DeleteTopicRequestInterface::class => autowire(DeleteTopicRequest::class),
    DeleteBoardRequestInterface::class => autowire(DeleteBoardRequest::class),
    SetTopicStickyRequestInterface::class => autowire(SetTopicStickyRequest::class),
    UnsetTopicStickyRequestInterface::class => autowire(UnsetTopicStickyRequest::class),
    SignupRequestInterface::class => autowire(SignupRequest::class),
    DeclineApplicationRequestInterface::class => autowire(DeclineApplicationRequest::class),
    AcceptApplicationRequestInterface::class => autowire(AcceptApplicationRequest::class),
    KickPlayerRequestInterface::class => autowire(KickPlayerRequest::class),
    PromotePlayerRequestInterface::class => autowire(PromotePlayerRequest::class),
    CreateAllianceRequestInterface::class => autowire(CreateAllianceRequest::class),
    EditDetailsRequestInterface::class => autowire(EditDetailsRequest::class),
    CreateRelationRequestInterface::class => autowire(CreateRelationRequest::class),
    SuggestPeaceRequestInterface::class => autowire(SuggestPeaceRequest::class),
    CancelOfferRequestInterface::class => autowire(CancelOfferRequest::class),
    AcceptOfferRequestInterface::class => autowire(AcceptOfferRequest::class),
    CancelContractRequestInterface::class => autowire(CancelContractRequest::class),
    AllianceUiFactoryInterface::class => autowire(AllianceUiFactory::class),
    'ALLIANCE_ACTIONS' => [
        AddBoard::ACTION_IDENTIFIER => autowire(AddBoard::class),
        CreateTopic::ACTION_IDENTIFIER => autowire(CreateTopic::class),
        CreatePost::ACTION_IDENTIFIER => autowire(CreatePost::class),
        RenameTopic::ACTION_IDENTIFIER => autowire(RenameTopic::class),
        RenameBoard::ACTION_IDENTIFIER => autowire(RenameBoard::class),
        DeleteTopic::ACTION_IDENTIFIER => autowire(DeleteTopic::class),
        DeleteBoard::ACTION_IDENTIFIER => autowire(DeleteBoard::class),
        DeletePost::ACTION_IDENTIFIER => autowire(DeletePost::class),
        EditPost::ACTION_IDENTIFIER => autowire(EditPost::class),
        SetTopicSticky::ACTION_IDENTIFIER => autowire(SetTopicSticky::class),
        UnsetTopicSticky::ACTION_IDENTIFIER => autowire(UnsetTopicSticky::class),
        Signup::ACTION_IDENTIFIER => autowire(Signup::class),
        DeclineApplication::ACTION_IDENTIFIER => autowire(DeclineApplication::class),
        AcceptApplication::ACTION_IDENTIFIER => autowire(AcceptApplication::class),
        KickPlayer::ACTION_IDENTIFIER => autowire(KickPlayer::class),
        PromotePlayer::ACTION_IDENTIFIER => autowire(PromotePlayer::class),
        Leave::ACTION_IDENTIFIER => autowire(Leave::class),
        CreateAlliance::ACTION_IDENTIFIER => autowire(CreateAlliance::class),
        EditDetails::ACTION_IDENTIFIER => autowire(EditDetails::class),
        CreateRelation::ACTION_IDENTIFIER => autowire(CreateRelation::class),
        SuggestPeace::ACTION_IDENTIFIER => autowire(SuggestPeace::class),
        CancelOffer::ACTION_IDENTIFIER => autowire(CancelOffer::class),
        AcceptOffer::ACTION_IDENTIFIER => autowire(AcceptOffer::class),
        DeclineOffer::ACTION_IDENTIFIER => autowire(DeclineOffer::class),
        CancelContract::ACTION_IDENTIFIER => autowire(CancelContract::class),
        ChangeAvatar::ACTION_IDENTIFIER => autowire(ChangeAvatar::class),
        DeleteAvatar::ACTION_IDENTIFIER => autowire(DeleteAvatar::class),
        DeleteAlliance::ACTION_IDENTIFIER => autowire(DeleteAlliance::class),
        DemotePlayer::ACTION_IDENTIFIER => autowire(DemotePlayer::class)
            ->constructorParameter(
                'demotePlayerRequest',
                autowire(DemotePlayerRequest::class),
            ),
    ],
    'ALLIANCE_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        Boards::VIEW_IDENTIFIER => autowire(Boards::class),
        NewTopic::VIEW_IDENTIFIER => autowire(NewTopic::class),
        Board::VIEW_IDENTIFIER => autowire(Board::class),
        Topic::VIEW_IDENTIFIER => autowire(Topic::class),
        NewPost::VIEW_IDENTIFIER => autowire(NewPost::class),
        ShowEditPost::VIEW_IDENTIFIER => autowire(ShowEditPost::class),
        TopicSettings::VIEW_IDENTIFIER => autowire(TopicSettings::class),
        BoardSettings::VIEW_IDENTIFIER => autowire(BoardSettings::class),
        Applications::VIEW_IDENTIFIER => autowire(Applications::class),
        Management::VIEW_IDENTIFIER => autowire(Management::class),
        Create::VIEW_IDENTIFIER => autowire(Create::class),
        Edit::VIEW_IDENTIFIER => autowire(Edit::class),
        Relations::VIEW_IDENTIFIER => autowire(Relations::class),
        ShowMemberRumpInfo::VIEW_IDENTIFIER => autowire(ShowMemberRumpInfo::class),
        DiplomaticRelations::VIEW_IDENTIFIER => autowire(DiplomaticRelations::class),
    ]
];
