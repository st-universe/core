<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnComments;

use Noodlehaus\ConfigInterface;
use Stu\Orm\Entity\KnCommentInterface;
use Stu\Orm\Entity\UserInterface;

final class KnCommentTal implements KnCommentTalInterface
{
    private KnCommentInterface $comment;

    private UserInterface $currentUser;

    private ConfigInterface $config;

    public function __construct(
        ConfigInterface $config,
        KnCommentInterface $comment,
        UserInterface $currentUser
    ) {
        $this->comment = $comment;
        $this->currentUser = $currentUser;
        $this->config = $config;
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
        if ($commentUserName !== '') {
            return $commentUserName;
        }

        return $this->comment->getUser()->getName();
    }

    public function getUserAvatarPath(): string
    {
        if ($this->comment->getUserName() !== '') {
            return '';
        }

        if ($this->comment->getUser()->getAvatar() === '') {
            return sprintf(
                'assets/rassen/%skn.png',
                $this->comment->getUser()->getFactionId()
            );
        } else {

            return sprintf(
                '/%s/%s.png',
                $this->config->get('game.user_avatar_path'),
                $this->comment->getUser()->getAvatar()
            );
        }
    }

    public function isDeleteable(): bool
    {
        return $this->comment->getUser() === $this->currentUser;
    }

    public function getUser(): UserInterface
    {
        return $this->comment->getUser();
    }
}
