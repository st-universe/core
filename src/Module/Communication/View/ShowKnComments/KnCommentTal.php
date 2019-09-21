<?php

// @todo activate strict typing
declare(strict_types=0);

namespace Stu\Module\Communication\View\ShowKnComments;

use Stu\Orm\Entity\KnCommentInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

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
        // @todo refactor
        global $container;

        return $container->get(UserRepositoryInterface::class)->find($this->getUserId())->getUser();
    }

    public function getUserAvatarPath(): string
    {
        if ($this->comment->getUserName()) {
            return '';
        }
        // @todo refactor
        global $container;

        return $container->get(UserRepositoryInterface::class)->find($this->getUserId())->getFullAvatarPath();
    }

    public function isDeleteable(): bool
    {
        return $this->getUserId() === $this->userId || $this->post->getUserId() === $this->userId;
    }
}