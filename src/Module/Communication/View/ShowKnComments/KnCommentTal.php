<?php

// @todo activate strict typing
declare(strict_types=0);

namespace Stu\Module\Communication\View\ShowKnComments;

use Stu\Orm\Entity\KnCommentInterface;
use Stu\Orm\Entity\KnPostInterface;

final class KnCommentTal implements KnCommentTalInterface
{
    private $comment;

    private $post;

    private $userId;

    public function __construct(
        KnCommentInterface $comment,
        KnPostInterface $post,
        int $userId
    ) {
        $this->comment = $comment;
        $this->post = $post;
        $this->userId = $userId;
    }

    public function getId(): int
    {
        return $this->comment->getId();
    }

    public function getPostId(): int
    {
        return $this->comment->getPostId();
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
        return $this->comment->getUserId();
    }

    public function getDisplayUserName(): string
    {
        if ($this->comment->getUserName()) {
            return $this->comment->getUserName();
        }
        return ResourceCache()->getUser($this->getUserId())->getName();
    }

    public function getUserAvatarPath(): string
    {
        if ($this->comment->getUserName()) {
            return '';
        }
        return ResourceCache()->getUser($this->getUserId())->getFullAvatarPath();
    }

    public function isDeleteable(): bool
    {
        return $this->getUserId() === $this->userId || $this->post->getUserId() === $this->userId;
    }
}