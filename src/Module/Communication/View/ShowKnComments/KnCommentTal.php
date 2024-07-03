<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnComments;

use Override;
use Noodlehaus\ConfigInterface;
use Stu\Orm\Entity\KnCommentInterface;
use Stu\Orm\Entity\UserInterface;

final class KnCommentTal implements KnCommentTalInterface
{
    public function __construct(private ConfigInterface $config, private KnCommentInterface $comment, private UserInterface $currentUser)
    {
    }

    #[Override]
    public function getId(): int
    {
        return $this->comment->getId();
    }

    #[Override]
    public function getPostId(): int
    {
        return $this->comment->getPosting()->getId();
    }

    #[Override]
    public function getText(): string
    {
        return $this->comment->getText();
    }

    #[Override]
    public function getDate(): int
    {
        return $this->comment->getDate();
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->comment->getUser()->getId();
    }

    #[Override]
    public function getDisplayUserName(): string
    {
        $commentUserName = $this->comment->getUserName();
        if ($commentUserName !== '') {
            return $commentUserName;
        }

        return $this->comment->getUser()->getName();
    }

    #[Override]
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

    #[Override]
    public function isDeleteable(): bool
    {
        return $this->comment->getUser() === $this->currentUser;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->comment->getUser();
    }
}