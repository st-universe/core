<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\PostKnComment;

use Stu\Module\Communication\View\ShowKnComments\ShowKnComments;
use Stu\Module\Communication\View\ShowSingleKn\ShowSingleKn;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\KnCommentRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class PostKnComment implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_POST_COMMENT';
    public const CHARACTER_LIMIT = 250;

    private PostKnCommentRequestInterface $postKnCommentRequest;

    private KnCommentRepositoryInterface $knCommentRepository;

    private KnPostRepositoryInterface $knPostRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        PostKnCommentRequestInterface $postKnCommentRequest,
        KnCommentRepositoryInterface $knCommentRepository,
        KnPostRepositoryInterface $knPostRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->postKnCommentRequest = $postKnCommentRequest;
        $this->knCommentRepository = $knCommentRepository;
        $this->knPostRepository = $knPostRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowKnComments::VIEW_IDENTIFIER);

        /** @var KnPostInterface $post */
        $post = $this->knPostRepository->find($this->postKnCommentRequest->getPostId());

        if ($post === null) {
            return;
        }

        $text = $this->postKnCommentRequest->getText();

        if (mb_strlen($text) < 3) {
            return;
        }
        if (mb_strlen($text) > static::CHARACTER_LIMIT) {
            return;
        }
        $obj = $this->knCommentRepository->prototype()
            ->setUser($game->getUser())
            ->setDate(time())
            ->setPosting($post)
            ->setText($text);

        $this->knCommentRepository->save($obj);

        $notificatedPlayers = [$game->getUser()->getId()];

        // send notification to post owner
        if ($game->getUser() !== $post->getUser()) {
            $notificatedPlayers[] = $post->getUserId();

            $text = sprintf(
                _('Der Spieler %s hat deinen KN-Beitrag (%d) kommentiert.'),
                $game->getUser()->getName(),
                $post->getId()
            );

            $href = sprintf(
                _('comm.php?%s=1&id=%d'),
                ShowSingleKn::VIEW_IDENTIFIER,
                $post->getId()
            );

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $post->getUserId(),
                $text,
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                $href
            );
        }


        // send notifications to other commentators
        foreach ($post->getComments() as $comment) {
            $commentatorId = $comment->getUser()->getId();

            if (!isset($commentatorId)) {
                continue;
            }

            if (!in_array($commentatorId, $notificatedPlayers)) {
                $notificatedPlayers[] = $commentatorId;

                $text = sprintf(
                    _('Der Spieler %s hat einen KN-Beitrag (%d) kommentiert, den du ebenfalls kommentiert hast.'),
                    $game->getUser()->getName(),
                    $post->getId()
                );

                $href = sprintf(
                    _('comm.php?%s=1&id=%d'),
                    ShowSingleKn::VIEW_IDENTIFIER,
                    $post->getId()
                );

                $this->privateMessageSender->send(
                    UserEnum::USER_NOONE,
                    $commentatorId,
                    $text,
                    PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                    $href
                );
            }
        }
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
