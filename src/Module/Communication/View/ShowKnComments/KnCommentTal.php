<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnComments;

use Stu\Orm\Entity\KnCommentInterface;
use Stu\Orm\Entity\UserInterface;

final class KnCommentTal implements KnCommentTalInterface
{
    private KnCommentInterface $comment;

    private UserInterface $currentUser;

    public function __construct(
        KnCommentInterface $comment,
        UserInterface $currentUser
    ) {
        $this->comment = $comment;
        $this->currentUser = $currentUser;
    }

    public function getId(): int
    {
        return $this->comment->getId();
    }

    public function getPostId(): int
    {
        return $this->comment->getPosting()->getId();
    }

    public function getText(): string
    {
        return $this->comment->getText();
    }

    public function getDate(): int
    {
        return $this->comment->getDate();
    }

    public function getUserId(): int
    {
        return $this->comment->getUser()->getId();
    }

    public function getDisplayUserName(): string
    {
        $commentUserName = $this->comment->getUserName();
        if ($commentUserName) {
            return $commentUserName;
        }

        return $this->comment->getUser()->getUserName();
    }

    public function getUserAvatarPath(): string
    {
        if ($this->comment->getUserName()) {
            return '';
        }

        return $this->comment->getUser()->getFullAvatarPath();
    }

    public function isDeleteable(): bool
    {
        return $this->comment->getUser() === $this->currentUser;
    }
}
