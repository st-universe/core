<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\PostKnComment;

use Override;
use Stu\Module\Communication\View\ShowKnComments\ShowKnComments;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\KnCommentRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class PostKnComment implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_POST_COMMENT';
    public const int CHARACTER_LIMIT = 250;

    public function __construct(private PostKnCommentRequestInterface $postKnCommentRequest, private KnCommentRepositoryInterface $knCommentRepository, private KnPostRepositoryInterface $knPostRepository, private PrivateMessageSenderInterface $privateMessageSender) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowKnComments::VIEW_IDENTIFIER);

        $post = $this->knPostRepository->find($this->postKnCommentRequest->getKnId());
        if ($post === null) {
            return;
        }

        $text = $this->postKnCommentRequest->getText();

        if (mb_strlen($text) < 3) {
            return;
        }
        if (mb_strlen($text) > self::CHARACTER_LIMIT) {
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

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $post->getUserId(),
                $text,
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                $post
            );
        }

        // send notifications to other commentators
        foreach ($post->getComments() as $comment) {
            $commentatorId = $comment->getUser()->getId();

            if (!in_array($commentatorId, $notificatedPlayers)) {
                $notificatedPlayers[] = $commentatorId;

                $text = sprintf(
                    _('Der Spieler %s hat einen KN-Beitrag (%d) kommentiert, den du ebenfalls kommentiert hast.'),
                    $game->getUser()->getName(),
                    $post->getId()
                );

                $this->privateMessageSender->send(
                    UserEnum::USER_NOONE,
                    $commentatorId,
                    $text,
                    PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                    $post
                );
            }
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
